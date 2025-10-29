<?php

namespace App\Listeners\Order;

use App\Enums\OrderStatus;
use App\Events\OrderStatusChanged;
use App\Listeners\BaseListener;
use App\Notifications\OrderCreatedNotification;
use App\Repositories\Contracts\DistributionCenterRepositoryInterface;
use Illuminate\Support\Facades\Notification;

class SendOrderPaidNotification extends BaseListener
{
    public function __construct(
        private readonly DistributionCenterRepositoryInterface $distributionCenterRepository
    ) {
    }

    /**
     * Get unique identifiers for this event
     *
     * @param  OrderStatusChanged  $event
     * @return array{order_id: int, old_status: string|null, new_status: string, event_type: string}
     */
    protected function getEventIdentifiers($event): array
    {
        return [
            'order_id' => $event->order->id,
            'old_status' => $event->oldStatus?->value,
            'new_status' => $event->newStatus->value,
            'event_type' => 'order_paid_notification',
        ];
    }

    /**
     * Handle the event - Send notifications when order status changes to PAID
     *
     * @param  OrderStatusChanged  $event
     * @return void
     */
    protected function handleEvent($event): void
    {
        /** @var OrderStatusChanged $event */

        // Only send notifications when order becomes PAID
        if ($event->newStatus->value !== OrderStatus::PAID()->value) {
            return;
        }

        $order = $event->order;

        $this->notifyCustomer($order);

        $this->notifyManagers($order);
    }

    /**
     * Send notification to the customer
     */
    private function notifyCustomer($order): void
    {
        if ($order->customer && $order->customer->user) {
            $order->customer->user->notify(new OrderCreatedNotification($order));
        }
    }

    /**
     * Send notification to center managers
     */
    private function notifyManagers($order): void
    {
        $managers = $this->distributionCenterRepository->getCenterManagers(
            $order->distribution_center_id
        );

        if ($managers->isNotEmpty()) {
            Notification::send($managers, new OrderCreatedNotification($order));
        }
    }
}
