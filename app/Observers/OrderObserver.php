<?php

namespace App\Observers;

use App\Enums\OrderStatus;
use App\Events\OrderStatusChanged;
use App\Models\Order;

class OrderObserver
{
    public function created(Order $order): void
    {
        // Dispatch event for order creation (treat as status change to 'confirmed' or current status)
        event(new OrderStatusChanged($order, null, $order->status));
    }

    public function updated(Order $order): void
    {
        if ($order->wasChanged('status')) {
            $oldStatus = $order->getOriginal('status');
            $newStatus = $order->status;

            // The original status is a string, convert to enum
            $oldStatusEnum = $oldStatus ? OrderStatus::from($oldStatus) : null;

            event(new OrderStatusChanged($order, $oldStatusEnum, $newStatus));
        }
    }
}
