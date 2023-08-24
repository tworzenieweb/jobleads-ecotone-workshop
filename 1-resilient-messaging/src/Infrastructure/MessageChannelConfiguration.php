<?php

declare(strict_types=1);

namespace App\Infrastructure;

use Ecotone\Dbal\DbalBackedMessageChannelBuilder;
use Ecotone\Messaging\Attribute\ServiceContext;
use Ecotone\Messaging\Channel\PollableChannel\GlobalPollableChannelConfiguration;

final class MessageChannelConfiguration
{
    /** This is for first part of the workshop */
    #[ServiceContext]
    public function ordersMessageChannel()
    {
        /**
         *  @TODO Add configuration for asynchronous message channel here
         */

        return [];
    }

    /** This is for second part of the workshop */
    #[ServiceContext]
    public function notificationsMessageChannel()
    {
        return DbalBackedMessageChannelBuilder::create('notifications');
    }


    #[ServiceContext]
    public function asyncChannelConfiguration()
    {
        return GlobalPollableChannelConfiguration::createWithDefaults()->withErrorChannel("dbal_dead_letter");
    }
}