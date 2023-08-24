<?php

declare(strict_types=1);

namespace App\Infrastructure;

use Ecotone\Amqp\Configuration\AmqpMessageConsumerConfiguration;
use Ecotone\Amqp\Distribution\AmqpDistributedBusConfiguration;
use Ecotone\Dbal\Configuration\DbalConfiguration;
use Ecotone\Messaging\Attribute\ServiceContext;
use Ecotone\Messaging\Handler\Recoverability\ErrorHandlerConfiguration;
use Ecotone\Messaging\Handler\Recoverability\RetryTemplateBuilder;

/**
 * Predefined configuration for Ecotone.
 * We do not need to configure anything here for the need of the task.
 */
final class EcotoneConfiguration
{
    #[ServiceContext]
    public function retryConfiguration(): ErrorHandlerConfiguration
    {
        /**
         * This is configuration for retrying failed messages.
         * We've set max of 3 retry attempt and then we move message to dbal dead letter.
         */
        return ErrorHandlerConfiguration::createWithDeadLetterChannel(
            'errorChannel',
            RetryTemplateBuilder::exponentialBackoff(2000, 2)
                ->maxRetryAttempts(3),
            'dbal_dead_letter'
        );
    }

    #[ServiceContext]
    public function distributedConsumer(): AmqpDistributedBusConfiguration
    {
        /**
         * Distributed Consumer. Needed for communication with Ecotone Pulse
         */
        return AmqpDistributedBusConfiguration::createConsumer();
    }

    #[ServiceContext]
    public function orderChannelDefinition()
    {
        return \Ecotone\Amqp\AmqpBackedMessageChannelBuilder::create("orders");
    }
}