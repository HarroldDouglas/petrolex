<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\OrderDeliveredEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

final class LogOrderDelivered implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(OrderDeliveredEvent $event): void
    {
        Log::info('Order status changed to DELIVERED', [
            'order_id' => $event->order->id,
            'customer_id' => $event->order->customer_id,
            'total_amount' => $event->order->total_amount,
            'delivered_at' => now()->toDateTimeString(),
        ]);
    }
}
