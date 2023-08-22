<?php

declare(strict_types=1);

namespace App\Domain;

use App\Domain\Event\OrderWasCancelled;
use App\Domain\Event\OrderWasPlaced;
use Ecotone\Messaging\Attribute\Asynchronous;
use Ecotone\Modelling\Attribute\EventHandler;
use Ecotone\Modelling\Attribute\QueryHandler;

final class NotificationService
{
    private bool $hasNotificationBeenSent = false;

    #[Asynchronous("notifications")]
    #[EventHandler(endpointId: 'orderCancelledNotifications')]
    public function whenOrderWasCancelled(OrderWasPlaced|OrderWasCancelled $event): void
    {
        $this->hasNotificationBeenSent = true;
    }

    #[QueryHandler('hasNotificationBeenSent')]
    public function isSuccessful(): bool
    {
        return $this->hasNotificationBeenSent;
    }
}