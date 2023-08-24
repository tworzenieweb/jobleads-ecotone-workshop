<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\Order;
use App\Domain\OrderRepository;
use App\Domain\OrderWasCancelled;
use App\Domain\OrderWasPlaced;
use App\Domain\ShippingService;
use Ecotone\Messaging\Attribute\Asynchronous;
use Ecotone\Modelling\Attribute\CommandHandler;
use Ecotone\Modelling\Attribute\EventHandler;
use Ecotone\Modelling\EventBus;

final class OrderService
{
    #[CommandHandler]
    public function placeOrder(
        PlaceOrder $placeOrder, OrderRepository $orderRepository,
        EventBus $eventBus
    ): void
    {
        $order = Order::create($placeOrder->orderId, $placeOrder->productName);
        $orderRepository->save($order);

        $eventBus->publish(new OrderWasPlaced($placeOrder->orderId));
    }

    #[CommandHandler]
    public function cancelOrder(
        CancelOrder $command, OrderRepository $orderRepository,
        EventBus $eventBus
    ) {
        $order = $orderRepository->get($command->orderId);
        $order->cancel();
        $orderRepository->save($order);

        $eventBus->publish(new OrderWasCancelled($command->orderId));
    }

    #[Asynchronous("orders")]
    #[EventHandler(endpointId: "order_was_placed")]
    public function when(
        OrderWasPlaced $orderWasPlaced,
        OrderRepository $orderRepository,
        ShippingService $shippingService
    )
    {
        $shippingService->ship($orderRepository->get($orderWasPlaced->orderId));
    }
}