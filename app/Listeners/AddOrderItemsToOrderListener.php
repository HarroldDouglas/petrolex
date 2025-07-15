<?php

namespace App\Listeners;

use App\Events\OrderCreatedEvent;

class AddOrderItemsToOrderListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrderCreatedEvent $event): void
    {
        foreach ($event->orderItemsData as $itemData) {
            $event->order->items()->create($itemData);
        }
    }
}
