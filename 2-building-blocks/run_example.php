<?php

use App\Application\CancelOrder;
use App\Application\PlaceOrder;
use App\Domain\OrderRepository;
use App\Domain\ShippingService;
use App\Infrastructure\NetworkFailingShippingService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
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
use Enqueue\Dbal\DbalDestination;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;

require __DIR__ . "/vendor/autoload.php";

$ecotoneLite = bootstrapEcotone();
$notificationsPollingMetadata = ExecutionPollingMetadata::createWithDefaults()->withExecutionTimeLimitInMilliseconds(2000)->withHandledMessageLimit(4);

try {
    $orderId = Uuid::uuid4()->toString();
    comment("Placing order");
    $ecotoneLite->getCommandBus()->send(new PlaceOrder($orderId, "Laptop"));
    comment("Order was placed");
    comment('Cancelling order');
    if (@class_exists(DoctrineORMOrderRepository::class)) {
        $ecotoneLite->getCommandBus()->send(new CancelOrder($orderId));
    }else {
        $ecotoneLite->getCommandBus()->sendWithRouting("order.cancel", metadata: ["aggregate.id" => $orderId]);
    }
    comment('Order was cancelled');

    comment('Running asynchronous notifications');
    $ecotoneLite->run('notifications', $notificationsPollingMetadata);
    Assert::assertTrue($ecotoneLite->getQueryBus()->sendWithRouting("hasNotificationBeenSent"), "Notification has not been sent");

    comment("\nOrder was placed and cancelled, notification has been sent, everything worked as expected.\n");
}catch (\Exception $exception) {
    echo "\n\033[31mWe have failed to cancel the Order. Business just lost credibility in customer's eyes.\033[0m\n";
    echo "\033[31merror:\033[0m " . $exception->getMessage() . "\n";
    echo "\033[31mfile:\033[0m " . $exception->getFile() . "\n";
}

function comment(string $comment): void
{
    echo sprintf("\033[38;5;220mcomment:\033[0m %s\n", $comment);
}

function bootstrapEcotone(): ConfiguredMessagingSystem
{
    $connection = (new DbalConnectionFactory(getenv('DATABASE_DSN') ? getenv('DATABASE_DSN') : 'pgsql://ecotone:secret@localhost:4002/ecotone'))->createContext()->getDbalConnection();
    $entityManager = EntityManager::create($connection, ORMSetup::createAttributeMetadataConfiguration([__DIR__ . '/src/Domain'], true));

    if (! $connection->createSchemaManager()->tablesExist('orders')) {
        $connection->executeStatement(<<<SQL
            CREATE TABLE orders (
                order_id UUID PRIMARY KEY,
                product_name VARCHAR(255),
                is_cancelled BOOLEAN
            )
        SQL);
    }

    $services = [
        DbalConnectionFactory::class => DbalConnection::createEntityManager($entityManager),
        EntityManagerInterface::class => $entityManager,
        'logger' => new EchoLogger()
    ];

    if (@class_exists(DoctrineORMOrderRepository::class)) {
        $services[OrderRepository::class] = new DoctrineORMOrderRepository($entityManager);
    }

    return EcotoneLiteApplication::bootstrap(
        $services,
        pathToRootCatalog: __DIR__
    );
}