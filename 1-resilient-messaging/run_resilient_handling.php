<?php

use App\Application\PlaceOrder;
use App\Domain\OrderRepository;
use App\Domain\ShippingService;
use App\Infrastructure\DoctrineORMOrderRepository;
use App\Infrastructure\NetworkFailingShippingService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Ecotone\Dbal\DbalConnection;
use Ecotone\Dbal\Recoverability\DeadLetterGateway;
use Ecotone\Lite\EcotoneLiteApplication;
use Ecotone\Messaging\Config\ConfiguredMessagingSystem;
use Ecotone\Messaging\Config\ServiceConfiguration;
use Ecotone\Messaging\Endpoint\ExecutionPollingMetadata;
use Ecotone\Messaging\Handler\Logger\EchoLogger;
use Enqueue\AmqpExt\AmqpConnectionFactory;
use Enqueue\Dbal\DbalConnectionFactory;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;

require __DIR__ . "/vendor/autoload.php";

$serviceName = 'resilient_service';
$ecotoneLite = bootstrapEcotone($serviceName);
$orderPollingMetadata = ExecutionPollingMetadata::createWithDefaults()->withExecutionTimeLimitInMilliseconds(10000)->withHandledMessageLimit(4);
$distributedPollingMetadata = ExecutionPollingMetadata::createWithDefaults()->withExecutionTimeLimitInMilliseconds(60000)->withHandledMessageLimit(1);
cleanup($ecotoneLite, $serviceName);

try {
    comment("Placing an order");
    $ecotoneLite->getCommandBus()->send(new PlaceOrder(Uuid::uuid4()->toString(), "Laptop"));

    comment("Ecotone will create a queue for you in RabbitMQ (http://localhost:4001/#/queues - guest:guest) and serialize/deserialize messages.");
    comment("Awaiting for message to be processed.");
    $ecotoneLite->run("orders", $orderPollingMetadata);

    comment("Retry message from Ecotone Pulse: http://localhost:4000, to finish the task :)");
    comment('Awaiting for message to be processed.');
    $ecotoneLite->run($serviceName, $distributedPollingMetadata);

    comment('Message replayed from Dead Letter back to `orders` channel. We are processing it again.');
    $ecotoneLite->run('orders', $orderPollingMetadata);

    Assert::assertTrue($ecotoneLite->getQueryBus()->sendWithRouting("isShippingSuccessful"), "Message was not replayed from Ecotone Pulse");
    comment("We have managed to recover from failure and order is stored and delivered to the customer. Congratulations, task completed!\n");
}catch (\Exception $exception) {
    echo "\n\033[31mWe have failed to place and deliver the Order to the customer, business just lost the money.\033[0m\n";
    echo "\033[31merror:\033[0m " . $exception->getMessage() . "\n";
    echo "\033[31mfile:\033[0m " . $exception->getFile() . "\n";
}

function cleanup(ConfiguredMessagingSystem $ecotoneLite, string $serviceName): void
{
    /** @var AmqpConnectionFactory $amqpConnectionFactory */
    $amqpConnectionFactory = $ecotoneLite->getServiceFromContainer(AmqpConnectionFactory::class);
    $amqpConnectionFactory->createContext()->deleteQueue(new \Interop\Amqp\Impl\AmqpQueue('orders'));
    $amqpConnectionFactory->createContext()->deleteQueue(new \Interop\Amqp\Impl\AmqpQueue('distributed_' . $serviceName));
    $ecotoneLite->getGatewayByName(DeadLetterGateway::class)->deleteAll();
}

function comment(string $comment): void
{
    echo sprintf("\033[38;5;220mcomment:\033[0m %s\n", $comment);
}

function bootstrapEcotone(string $serviceName): ConfiguredMessagingSystem
{
    $shippingService = new NetworkFailingShippingService();
    $connection = (new DbalConnectionFactory(getenv('DATABASE_DSN') ? getenv('DATABASE_DSN') : 'pgsql://ecotone:secret@localhost:4002/ecotone'))->createContext()->getDbalConnection();
    $entityManager = EntityManager::create($connection, ORMSetup::createAttributeMetadataConfiguration([__DIR__ . '/src/Domain'], true));

    if (! $connection->createSchemaManager()->tablesExist('orders')) {
        $connection->executeStatement(<<<SQL
            CREATE TABLE orders (
                order_id UUID PRIMARY KEY,
                product_name VARCHAR(255)
            )
        SQL);
    }

    return EcotoneLiteApplication::bootstrap(
        [
            ShippingService::class => $shippingService,
            OrderRepository::class => new DoctrineORMOrderRepository($entityManager),
            NetworkFailingShippingService::class => $shippingService,
            DbalConnectionFactory::class => DbalConnection::createEntityManager($entityManager),
            AmqpConnectionFactory::class => new AmqpConnectionFactory(['dsn' => getenv('RABBIT_DSN') ? getenv('RABBIT_DSN') : 'amqp://guest:guest@localhost:4003/%2f']),
            'logger' => new EchoLogger()
        ],
        serviceConfiguration: ServiceConfiguration::createWithDefaults()
            ->withServiceName($serviceName)
            ->withDefaultErrorChannel('errorChannel'),
        pathToRootCatalog: __DIR__
    );
}