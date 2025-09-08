<?php

namespace App\Notifications;

use App\Mail\Order\OrderStatusChangedMail;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class OrderStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Order $order,
        public ?string $oldStatus = null,
        public ?string $newStatus = null
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return new OrderStatusChangedMail(
            $this->order,
            $notifiable,
            $this->oldStatus,
            $this->newStatus
        );
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'order_status_changed',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'customer_name' => $this->order->customer->user->name ?? 'Client',
            'total_amount' => $this->order->total_amount,
            'message' => __('email.order_status_changed_message', [
                'order_number' => $this->order->order_number,
                'old_status' => $this->getStatusLabel($this->oldStatus),
                'new_status' => $this->getStatusLabel($this->newStatus),
            ]),
        ];
    }

    private function getStatusLabel(?string $status): string
    {
        if (! $status) {
            return '';
        }

        return \App\Enums\OrderStatus::labels()[strtoupper($status)] ?? ucfirst($status);
    }
}
