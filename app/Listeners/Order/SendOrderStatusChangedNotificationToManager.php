<?php

namespace App\Listeners\Order;

use App\Events\OrderStatusChanged;
use App\Mail\Order\OrderStatusChangedForManager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendOrderStatusChangedNotificationToManager implements ShouldQueue
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
        $distributionCenter = $event->order->distributionCenter;

        if ($distributionCenter && $distributionCenter->manager?->email) {
            Mail::to($distributionCenter->manager->email)->send(new OrderStatusChangedForManager($event->order));
        }
    }
}
