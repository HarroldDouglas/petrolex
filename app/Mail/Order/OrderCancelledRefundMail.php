<?php

namespace App\Mail\Order;

use App\Models\Order;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderCancelledRefundMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public User $user,
        public float $refundAmount,
        public float $newBalance
    ) {
        if ($this->user->language) {
            app()->setLocale($this->user->language);
        }
    }

    public function envelope(): Envelope
    {
        $orderNumber = $this->order->order_number;

        if (! $this->user->email) {
            throw new \InvalidArgumentException("User {$this->user->id} does not have a valid email address");
        }

        return new Envelope(
            to: [$this->user->email],
            subject: __('email.order_cancelled_refund_subject', ['order_number' => $orderNumber]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.order-cancelled-refund',
            with: [
                'order' => $this->order,
                'user' => $this->user,
                'refundAmount' => $this->refundAmount,
                'newBalance' => $this->newBalance,
            ],
        );
    }
}
