<?php

namespace App\Notifications;

use App\Mail\Order\OrderCancelledRefundMail;
use App\Models\Order;
use Illuminate\Notifications\Notification;

class OrderCancelledRefundNotification extends Notification
{
    public function __construct(
        public Order $order,
        public float $refundAmount,
        public float $newBalance
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return new OrderCancelledRefundMail(
            $this->order,
            $notifiable,
            $this->refundAmount,
            $this->newBalance
        );
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'order_cancelled_refund',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'refund_amount' => $this->refundAmount,
            'new_balance' => $this->newBalance,
            'customer_name' => $this->order->customer->user->name ?? 'Client',
            'message' => __('email.order_cancelled_refund_message', [
                'order_number' => $this->order->order_number,
                'refund_amount' => number_format($this->refundAmount, 0, ',', ' '),
                'new_balance' => number_format($this->newBalance, 0, ',', ' '),
            ]),
        ];
    }
}
