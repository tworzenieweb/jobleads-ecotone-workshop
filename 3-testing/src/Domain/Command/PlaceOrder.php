<?php

declare(strict_types=1);

namespace App\Domain\Command;

final readonly class PlaceOrder
{
    public function __construct(
        public string $orderId,
        public string $productName
    ) {}
}