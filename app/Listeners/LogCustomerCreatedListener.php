<?php

namespace App\Listeners;

use App\Events\CustomerCreatedEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LogCustomerCreatedListener implements ShouldQueue
{
    use InteractsWithQueue;

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
    public function handle(CustomerCreatedEvent $event): void
    {
        Log::info('Customer created: '.$event->customer->id.' - '.$event->customer->user->full_name);
    }
}
