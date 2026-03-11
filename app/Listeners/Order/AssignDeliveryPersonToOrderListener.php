<?php

namespace App\Listeners\Order;

use App\Events\OrderCreatedEvent;
use App\Services\DeliveryPersonService;
use App\Services\Order\OrderService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AssignDeliveryPersonToOrderListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        private readonly DeliveryPersonService $deliveryPersonService,
        private readonly OrderService $orderService
    ) {}

    public function handle(OrderCreatedEvent $event): void
    {
        $order = $event->order;
        if ($order->delivery_person_id === null) {
            $deliveryPerson = $this->deliveryPersonService->findLeastBusyDeliveryPerson($order->distribution_center_id);

            if ($deliveryPerson) {
                $this->orderService->assignDeliveryPerson($order, $deliveryPerson->id);
            }
        }
    }
}
