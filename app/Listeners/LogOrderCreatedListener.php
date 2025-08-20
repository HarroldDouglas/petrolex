<?php

namespace App\Listeners;

use App\Events\OrderCreatedEvent;
use Illuminate\Support\Facades\Log;

class LogOrderCreatedListener
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
        Log::info('Order created: '.$event->order->id.' by customer '.$event->order->customer_id);
    }
}
