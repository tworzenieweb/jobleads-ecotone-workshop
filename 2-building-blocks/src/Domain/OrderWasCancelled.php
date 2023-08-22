<?php

declare(strict_types=1);

namespace App\Domain;

final readonly class OrderWasCancelled
{
    public function __construct(
        public string $orderId
    ) {}
}