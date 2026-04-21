<?php

namespace App\Mail\Order;

use App\Models\Order;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderAssignedToDeliveryPersonMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public User $user
    ) {
        if ($this->user->language) {
            app()->setLocale($this->user->language);
        }
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            to: [$this->user->email],
            subject: __('email.order_assigned_delivery_subject', ['order_number' => $this->order->order_number]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.order-assigned-delivery-person',
            with: [
                'order' => $this->order,
                'user' => $this->user,
            ],
        );
    }
}
