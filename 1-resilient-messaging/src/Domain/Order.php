<?php

declare(strict_types=1);

namespace App\Domain;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'orders')]
final class Order
{
    #[ORM\Id]
    #[ORM\Column(name: 'order_id', type: 'string')]
    private string $orderId;
    #[ORM\Column(name: 'product_name', type: 'string')]
    private string $productName;
    #[ORM\Column(name: 'is_cancelled', type: 'boolean')]
    private bool $isCancelled;

    public function __construct(
        string $orderId,
        string $productName
    ) {
        $this->orderId = $orderId;
        $this->productName = $productName;
        $this->isCancelled = false;
    }

    public static function create(string $userId, string $productName): self
    {
        return new self($userId, $productName);
    }

    public function cancel(): void
    {
        $this->isCancelled = true;
    }
}