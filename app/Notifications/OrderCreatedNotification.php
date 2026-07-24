<?php

namespace App\Notifications;

use App\Mail\Order\OrderCreatedMail;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class OrderCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Order $order
    ) {
        // Dispatch after the surrounding DB transaction commits, so a
        // mail/database failure can never roll back the payment/refund.
        $this->afterCommit = true;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): OrderCreatedMail
    {
        return new OrderCreatedMail($this->order, $notifiable);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'message' => __('email.order_created_message', [
                'order_number' => $this->order->order_number,
            ]),
            'type' => 'order_created',
        ];
    }
}
