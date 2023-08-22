<?php

declare(strict_types=1);

namespace App\Infrastructure;

use Ecotone\Amqp\Configuration\AmqpMessageConsumerConfiguration;
use Ecotone\Amqp\Distribution\AmqpDistributedBusConfiguration;
use Ecotone\Dbal\Configuration\DbalConfiguration;
use Ecotone\Messaging\Attribute\ServiceContext;
use Ecotone\Messaging\Channel\SimpleMessageChannelBuilder;
use Ecotone\Messaging\Handler\Recoverability\ErrorHandlerConfiguration;
use Ecotone\Messaging\Handler\Recoverability\RetryTemplateBuilder;

final class EcotoneConfiguration
{
    #[ServiceContext]
    public function notificationsChannel()
    {
        return SimpleMessageChannelBuilder::createQueueChannel('notifications');
    }
}