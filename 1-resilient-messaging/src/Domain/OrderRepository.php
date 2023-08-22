<?php

declare(strict_types=1);

namespace App\Domain;

use Ecotone\Modelling\Attribute\Repository;

interface OrderRepository
{
    public function get(string $orderId): Order;

    public function save(Order $order): void;
}