<?php

declare(strict_types=1);

namespace Tests\App;

use App\Domain\Command\PlaceOrder;
use App\Domain\Event\OrderWasPlaced;
use App\Domain\NotificationService;
use App\Domain\Order;
use Ecotone\Lite\EcotoneLite;
use Ecotone\Messaging\Channel\SimpleMessageChannelBuilder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class OrderFlowTest extends TestCase
{
    /**
     * @link https://docs.ecotone.tech/testing-support/testing-aggregates-and-sagas-with-message-flows#setting-up-flow-test
     */
    public function test_placing_an_order(): void
    {
        $orderId = Uuid::uuid4()->toString();
        $ecotoneLite = EcotoneLite::bootstrapFlowTesting(
            [Order::class]
        );

        /** @TODO Place new order */

        $this->assertNotNull(
            $ecotoneLite->getAggregate(Order::class, $orderId),
            "Order should be created"
        );
    }

    /**
     * @link https://docs.ecotone.tech/modelling/command-handling/state-stored-aggregate/aggregate-command-handlers#calling-aggregate-without-command-class
     */
    public function test_placing_an_order_and_cancelling_it(): void
    {
        $orderId = Uuid::uuid4()->toString();
        $ecotoneLite = EcotoneLite::bootstrapFlowTesting(
            [Order::class]
        );

        $ecotoneLite->sendCommand(new PlaceOrder($orderId, 'milk'));
        /** @TODO Cancel order */

        $this->assertTrue(
            $ecotoneLite
                ->sendQueryWithRouting('order.is_cancelled', metadata: ['aggregate.id' => $orderId]),
            "Order should be cancelled"
        );
    }

    /**
     * @link https://docs.ecotone.tech/testing-support/testing-messaging#verifying-published-events
     */
    public function test_event_is_published_when_order_was_placed(): void
    {
        $orderId = Uuid::uuid4()->toString();
        $ecotoneLite = EcotoneLite::bootstrapFlowTesting(
            [Order::class]
        );

        $ecotoneLite
            ->sendCommand(new PlaceOrder($orderId, 'milk'));

        /** @TODO fetch published events */
        $recordedEvents = [];

        $this->assertEquals(
            [new OrderWasPlaced($orderId)],
            $recordedEvents,
            'Order was placed event should be published'
        );
    }

    /**
     * @link https://docs.ecotone.tech/testing-support/testing-messaging#calling-buses
     */
    public function test_sending_notification_when_order_was_placed(): void
    {
        $orderId = Uuid::uuid4()->toString();
        $ecotoneLite = EcotoneLite::bootstrapFlowTesting(
            /** @TODO complete the configuration */
        );

        $this->assertTrue(
            $ecotoneLite
                ->publishEvent(new OrderWasPlaced($orderId))
                ->sendQueryWithRouting('hasNotificationBeenSent'),
            'Notification should be sent'
        );
    }

    /**
     * @link https://docs.ecotone.tech/testing-support/testing-messaging#calling-buses
     */
    public function test_when_order_is_placed_then_notification_will_be_triggered(): void
    {
        $orderId = Uuid::uuid4()->toString();
        $ecotoneLite = EcotoneLite::bootstrapFlowTesting(
            [Order::class, NotificationService::class],
            [new NotificationService()],
        );

        /** @TODO Pass using Command action */

        $this->assertTrue(
            $ecotoneLite
                ->sendQueryWithRouting('hasNotificationBeenSent'),
            'Notification should be sent'
        );
    }

    /**
     * @link https://docs.ecotone.tech/testing-support/testing-asynchronous-messaging#running-asynchronous-consumer
     */
    public function test_sending_event_to_notifications_channel(): void
    {
        $orderId = Uuid::uuid4()->toString();
        $ecotoneLite = EcotoneLite::bootstrapFlowTesting(
            [Order::class, NotificationService::class],
            [new NotificationService()],
            /** @TODO fill the configuration */
        );

        $ecotoneLite->sendCommand(new PlaceOrder($orderId, "milk"));

        $this->assertNotNull(
            $ecotoneLite
                ->getMessageChannel('notifications')->receive(),
            'Event was not sent to the notification channel'
        );
    }

    /**
     * @link https://docs.ecotone.tech/testing-support/testing-asynchronous-messaging#running-asynchronous-consumer
     */
    public function test_handling_event_from_notifications_channel(): void
    {
        $orderId = Uuid::uuid4()->toString();
        $ecotoneLite = EcotoneLite::bootstrapFlowTesting(
            [Order::class, NotificationService::class],
            [new NotificationService()],
            enableAsynchronousProcessing: [
                SimpleMessageChannelBuilder::createQueueChannel('notifications')
            ]
        );

        $ecotoneLite->sendCommand(new PlaceOrder($orderId, 'milk'));
        /** @TODO run the message consumer */

        $this->assertTrue(
            $ecotoneLite
                ->sendQueryWithRouting('hasNotificationBeenSent'),
            'Notification should be sent'
        );
    }
}