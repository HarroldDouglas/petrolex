<?php

namespace App\Observers;

use App\Enums\NotificationType;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\Shared\Notification\NotificationService;

class OrderObserver
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function created(Order $order): void
    {
        $this->notificationService->createOrderNotification(
            $order,
            NotificationType::ORDER_CREATED()
        );
    }

    public function updated(Order $order): void
    {
        if ($order->wasChanged('status')) {
            $notificationType = $this->getNotificationTypeFromStatus($order->status);

            if ($notificationType) {
                $this->notificationService->createOrderNotification(
                    $order,
                    $notificationType
                );
            }
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
