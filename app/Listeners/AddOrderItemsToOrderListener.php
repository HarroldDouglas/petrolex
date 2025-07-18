<?php

namespace App\Listeners;

use App\DTOs\Order\OrderItemDTO;
use App\Events\OrderCreatedEvent;

class AddOrderItemsToOrderListener
{
    /**
     * Handle the event.
     */
    public function handle(OrderCreatedEvent $event): void
    {
        $itemsForCreation = array_map(function (OrderItemDTO $dto) {
            $data = $dto->toArray();
            return $data;
        }, $event->orderItemsData);

        $event->order->items()->createMany($itemsForCreation);
    }
}
