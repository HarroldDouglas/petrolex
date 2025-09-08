<?php

namespace App\Mail\Order;

use App\Models\Order;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusChangedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public User $user,
        public ?string $oldStatus = null,
        public ?string $newStatus = null
    ) {}

    public function envelope(): Envelope
    {
        $orderNumber = $this->order->order_number;
        
        return new Envelope(
            to: [$this->user->email],
            subject: __('email.order_notification_subject', ['order_number' => $orderNumber]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.order-notification',
            with: [
                'order' => $this->order,
                'user' => $this->user,
                'oldStatus' => $this->oldStatus,
                'newStatus' => $this->newStatus,
            ],
        );
    }
}
