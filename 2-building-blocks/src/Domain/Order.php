<?php

declare(strict_types=1);

namespace App\Domain;

use App\Application\CancelOrder;
use App\Application\PlaceOrder;
use Doctrine\ORM\Mapping as ORM;
use Ecotone\Modelling\Attribute\Aggregate;
use Ecotone\Modelling\Attribute\CommandHandler;
use Ecotone\Modelling\Attribute\Identifier;
use Ecotone\Modelling\WithEvents;

#[Aggregate]
#[ORM\Entity]
#[ORM\Table(name: 'orders')]
final class Order
{
    use WithEvents;

    #[Identifier]
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

    #[CommandHandler]
    public static function create(PlaceOrder $placeOrder): self
    {
        return new self($placeOrder->orderId, $placeOrder->productName);
    }

    #[CommandHandler('order.cancel')]
    public function cancel(): void
    {
        $this->isCancelled = true;

        $this->recordThat(new OrderWasCancelled($this->orderId));
    }
}