<?php

namespace App\Observers;

use App\Enums\NotificationType;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Notifications\OrderNotification;
use App\Services\Shared\Notification\NotificationService;
use Illuminate\Support\Facades\Notification;

class OrderObserver
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function created(Order $order): void
    {
        $this->notifyOrderStakeholders($order, NotificationType::ORDER_CREATED());
    }

    public function updated(Order $order): void
    {
        if ($order->wasChanged('status')) {
            $notificationType = $this->getNotificationTypeFromStatus($order->status);

            if ($notificationType) {
                $this->notifyOrderStakeholders($order, $notificationType);
            }
        }

        if ($order->wasChanged() && ! $order->wasChanged('status')) {
            $this->notifyOrderStakeholders($order, NotificationType::ORDER_MODIFIED());
        }
    }

    private function notifyOrderStakeholders(Order $order, NotificationType $type): void
    {
        $usersToNotify = collect();

        if ($order->distributionCenter?->manager) {
            $usersToNotify->push($order->distributionCenter->manager);
        }

        if (in_array($type->value, ['order_confirmed', 'order_processing', 'order_delivered', 'order_cancelled'])) {
            if ($order->customer) {
                $usersToNotify->push($order->customer);
            }
        }

        $usersToNotify = $usersToNotify->filter()->unique('id');

        if ($usersToNotify->isNotEmpty()) {
            Notification::send($usersToNotify, new OrderNotification($order, $type));
        }
    }

    private function getNotificationTypeFromStatus(OrderStatus $status): ?NotificationType
    {
        return match ($status) {
            OrderStatus::CONFIRMED() => NotificationType::ORDER_CONFIRMED(),
            OrderStatus::PROCESSING() => NotificationType::ORDER_PROCESSING(),
            OrderStatus::DELIVERED() => NotificationType::ORDER_DELIVERED(),
            OrderStatus::CANCELLED() => NotificationType::ORDER_CANCELLED(),
            default => null,
        };
    }
}
