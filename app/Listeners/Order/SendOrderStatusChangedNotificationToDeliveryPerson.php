<?php

namespace App\Listeners\Order;

use App\Events\OrderStatusChanged;
use App\Mail\Order\OrderStatusChangedForDeliveryPerson;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendOrderStatusChangedNotificationToDeliveryPerson implements ShouldQueue
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
        $deliveryPerson = $event->order->deliveryPerson;

        if ($deliveryPerson && $deliveryPerson->user && $deliveryPerson->user->email) {
            Mail::to($deliveryPerson->user->email)->send(new OrderStatusChangedForDeliveryPerson($event->order));
        }
    }
}
