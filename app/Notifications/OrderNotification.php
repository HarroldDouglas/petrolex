<?php

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Models\Order;
use Illuminate\Notifications\Notification;

class OrderNotification extends Notification
{
    public function __construct(
        public Order $order,
        public NotificationType $type
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => $this->type->value,
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'customer_name' => $this->order->customer->name,
            'total_amount' => $this->order->total_amount,
            'message' => $this->generateMessage(),
            'url' => url('/orders/' . $this->order->id),
        ];
    }

    private function generateMessage(): string
    {
        return match ($this->type->value) {
            'order_created' => "Nouvelle commande {$this->order->order_number} créée par {$this->order->customer->name}",
            'order_confirmed' => "Commande {$this->order->order_number} confirmée",
            'order_processing' => "Commande {$this->order->order_number} en cours de traitement",
            'order_delivered' => "Commande {$this->order->order_number} livrée avec succès",
            'order_cancelled' => "Commande {$this->order->order_number} annulée",
            'order_modified' => "Commande {$this->order->order_number} modifiée",
            default => "Mise à jour pour la commande {$this->order->order_number}",
        };
    }
}
