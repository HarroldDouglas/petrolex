<?php

namespace App\Services\Shared\Notification;

use App\Enums\NotificationType;
use App\Models\Order;
use App\Models\User;
use App\Notifications\OrderNotification;
use Illuminate\Support\Collection as SupportCollection;

class NotificationService
{
    public function createOrderNotification(Order $order, NotificationType $type, User $user): void
    {
        $user->notify(new OrderNotification($order, $type));
    }

    public function createOrderNotificationForUsers(Order $order, NotificationType $type, array $users): void
    {
        foreach ($users as $user) {
            $this->createOrderNotification($order, $type, $user);
        }
    }

    public function createOrderNotificationForManager(Order $order, NotificationType $type): void
    {
        $manager = $order->distributionCenter->manager;

        if ($manager) {
            $this->createOrderNotification($order, $type, $manager);
        }
    }

    public function getUnreadNotifications(User $user, int $limit = 10): SupportCollection
    {
        return $user->unreadNotifications()
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get();
    }

    public function getUnreadCount(User $user): int
    {
        return $user->unreadNotifications()->count();
    }

    public function markAsRead(User $user, string $notificationId): void
    {
        $user->unreadNotifications()
            ->where('id', $notificationId)
            ->first()?->markAsRead();
    }

    public function markAllAsRead(User $user): void
    {
        $user->unreadNotifications()->update(['read_at' => now()]);
    }

    public function deleteNotification(User $user, string $notificationId): bool
    {
        $notification = $user->notifications()->where('id', $notificationId)->first();

        if ($notification) {
            return $notification->delete();
        }

        return false;
    }
}
