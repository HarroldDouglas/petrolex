<?php

namespace App\Listeners\Order;

use App\Enums\UserRole;
use App\Events\OrderCreatedEvent;
use App\Models\UserDistributionCenter;
use App\Notifications\OrderCreatedNotification;
use Illuminate\Support\Facades\Notification;

class SendOrderCreatedNotification
{
    /**
     * Handle the event.
     */
    public function handle(OrderCreatedEvent $event): void
    {
        $order = $event->order;

        $this->notifyCustomer($order);

        $this->notifyManagers($order);
    }

    /**
     * Send notification to the customer
     */
    private function notifyCustomer($order): void
    {
        if ($order->customer && $order->customer->user) {
            $order->customer->user->notify(new OrderCreatedNotification($order));
        }
    }

    /**
     * Send notification to center managers
     */
    private function notifyManagers($order): void
    {
        $managers = UserDistributionCenter::where('distribution_center_id', $order->distribution_center_id)
            ->with(['user' => function ($query) {
                $query->whereHas('roles', function ($roleQuery) {
                    $roleQuery->where('name', UserRole::CENTER_MANAGER()->value);
                });
            }])
            ->get()
            ->pluck('user')
            ->filter();

        if ($managers->isNotEmpty()) {
            Notification::send($managers, new OrderCreatedNotification($order));
        }
    }
}
