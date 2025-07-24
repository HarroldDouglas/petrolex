<?php

namespace App\Listeners\Order;

use App\Events\OrderStatusChanged;
use App\Mail\Order\OrderStatusChangedForCustomer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendOrderStatusChangedNotificationToCustomer implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct() {}

    /**
     * Handle the event.
     */
    public function handle(OrderStatusChanged $event): void
    {
        $customer = $event->order->customer;

        if ($customer && $customer->user && $customer->user->email) {
            Mail::to($customer->user->email)->send(new OrderStatusChangedForCustomer($event->order));
        }
    }
}
