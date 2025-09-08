<?php

namespace App\Listeners;

use App\Events\OrderCancelledEvent;
use App\Notifications\OrderCancelledNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

final class SendOrderCancelledNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(OrderCancelledEvent $event): void
    {
        $order = $event->order;

        if ($order->customer && $order->customer->user) {
            Notification::send($order->customer->user, new OrderCancelledNotification($order, true));
        }

        if ($order->distributionCenter && $order->distributionCenter->manager && $order->distributionCenter->manager->user) {
            Notification::send($order->distributionCenter->manager->user, new OrderCancelledNotification($order, false));
        }
    }
}
