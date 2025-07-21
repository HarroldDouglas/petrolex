<?php

namespace App\Listeners;

use App\Events\OrderCreatedEvent;

class DecrementStockListener
{
    /**
     * Handle the event.
     */
    public function handle(OrderCreatedEvent $event): void
    {
        // TODO Decrement stock for each order item

    }
}
