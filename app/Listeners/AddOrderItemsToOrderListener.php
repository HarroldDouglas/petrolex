<?php

namespace App\Listeners;

use App\Events\OrderCreatedEvent;

class AddOrderItemsToOrderListener
{
    /**
     * Handle the event.
     */
    public function handle(OrderCreatedEvent $event): void
    {
        $itemsForCreation = array_map(
            fn ($dto) => $dto->toArray(),
            $event->orderItemsData
        );

        $event->order->items()->createMany($itemsForCreation);
    }
}
