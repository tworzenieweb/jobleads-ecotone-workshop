<?php

declare(strict_types=1);

namespace App\Infrastructure;

use Ecotone\Dbal\Configuration\DbalConfiguration;
use Ecotone\Messaging\Attribute\ServiceContext;
use Ecotone\Messaging\Channel\SimpleMessageChannelBuilder;

final class EcotoneConfiguration
{
    #[ServiceContext]
    public function notificationsChannel()
    {
        return SimpleMessageChannelBuilder::createQueueChannel('notifications');
    }

    #[ServiceContext]
    public function getDbalConfiguration(): DbalConfiguration
    {
        return DbalConfiguration::createWithDefaults()
            ->withDoctrineORMRepositories(true);
    }
}