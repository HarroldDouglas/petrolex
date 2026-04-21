<?php

namespace App\Notifications;

use App\Mail\Order\OrderAssignedToDeliveryPersonMail;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderAssignedToDeliveryPersonNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Order $order
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): OrderAssignedToDeliveryPersonMail
    {
        return new OrderAssignedToDeliveryPersonMail($this->order, $notifiable);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'message' => __('email.order_assigned_delivery_notification_message', [
                'order_number' => $this->order->order_number,
            ]),
            'type' => 'order_assigned_delivery',
        ];
    }
}
