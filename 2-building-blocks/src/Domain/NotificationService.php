<?php

declare(strict_types=1);

namespace App\Domain;

use Ecotone\Messaging\Attribute\Asynchronous;
use Ecotone\Modelling\Attribute\EventHandler;
use Ecotone\Modelling\Attribute\QueryHandler;

final class NotificationService
{
    private bool $hasNotificationBeenSent = false;

    #[Asynchronous("notifications")]
    #[EventHandler(endpointId: 'orderCancelledNotifications')]
    public function whenOrderWasCancelled(OrderWasCancelled $orderWasCancelled): void
    {
        $this->hasNotificationBeenSent = true;
        echo "Sending notification about cancelling order\n";
    }

    #[QueryHandler('hasNotificationBeenSent')]
    public function isSuccessful(): bool
    {
        return $this->hasNotificationBeenSent;
    }
}