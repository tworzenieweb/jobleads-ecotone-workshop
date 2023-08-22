<?php

declare(strict_types=1);

namespace App\Infrastructure;

use App\Domain\Order;
use App\Domain\ShippingService;
use Ecotone\Modelling\Attribute\QueryHandler;

final class NetworkFailingShippingService implements ShippingService
{
    private int $counter = 0;
    private bool $isSuccessful = false;

    /**
     * This class imitates a network error that may occur during order shipping.
     * The error will occur 4 times, and then the order will be shipped correctly.
     */
    public function ship(Order $order): void
    {
        $this->counter++;

        if ($this->counter <= 4) {
            throw new \RuntimeException("Network error when calling Shipping Service.");
        }

        $this->isSuccessful = true;
    }

    #[QueryHandler("isShippingSuccessful")]
    public function isSuccessful(): bool
    {
        return $this->isSuccessful;
    }
}