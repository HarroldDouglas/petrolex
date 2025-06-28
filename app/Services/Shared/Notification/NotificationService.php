<?php

namespace App\Services\Shared\Notification;

use App\Enums\NotificationType;
use App\Models\Notification;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function createOrderNotification(Order $order, NotificationType $type): ?Notification
    {
        $manager = $order->distributionCenter->manager;

        if (! $manager) {
            // Log a warning or handle the case where no manager is found
            // For now, we'll just return null, meaning no notification is created for the manager.
            return null;
        }

        $data = $this->prepareOrderNotificationData($order, $type);

        return $this->createNotification(
            user: $manager,
            type: $type,
            data: $data
        );
    }

    private function createNotification(User $user, NotificationType $type, array $data): Notification
    {
        return Notification::create([
            'notifiable_type' => get_class($user),
            'notifiable_id' => $user->id,
            'type' => $type,
            'data' => $data,
        ]);
    }

    private function prepareOrderNotificationData(Order $order, NotificationType $type): array
    {
        return [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'customer_name' => $order->customer->name,
            'total_amount' => $order->total_amount,
            'message' => $this->generateMessage($order, $type),
            'url' => route('orders.details', $order->id),
        ];
    }

    private function generateMessage(Order $order, NotificationType $type): string
    {
        return match ($type) {
            NotificationType::ORDER_CREATED() => "Nouvelle commande {$order->order_number} créée par {$order->customer->name}",
            NotificationType::ORDER_DELIVERED() => "Commande {$order->order_number} livrée avec succès",
            NotificationType::ORDER_CANCELLED() => "Commande {$order->order_number} annulée",
            NotificationType::ORDER_MODIFIED() => "Commande {$order->order_number} modifiée",
            default => "Mise à jour pour la commande {$order->order_number}",
        };
    }

    public function markAsRead(int $notificationId, User $user): bool
    {
        $notification = $user->notifications()->find($notificationId);

        if ($notification) {
            $notification->markAsRead();

            return true;
        }

        return false;
    }

    public function deleteNotification(int $notificationId, User $user): bool
    {
        return $user->notifications()->where('id', $notificationId)->delete() > 0;
    }
}
