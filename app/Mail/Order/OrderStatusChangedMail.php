<?php

namespace App\Mail\Order;

use App\Enums\OrderStatus;
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
        public ?OrderStatus $oldStatus = null,
        public ?OrderStatus $newStatus = null
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

        // Get the appropriate subject based on order status
        $subjectKey = $this->getSubjectKey();

        return new Envelope(
            to: [$this->user->email],
            subject: __($subjectKey, ['order_number' => $orderNumber]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: $this->getTemplateView(),
            with: [
                'order' => $this->order,
                'user' => $this->user,
                'oldStatus' => $this->oldStatus,
                'newStatus' => $this->newStatus,
            ],
        );
    }

    /**
     * Get the appropriate email template based on order status
     */
    private function getTemplateView(): string
    {
        $status = $this->newStatus ?? $this->order->status;

        return match ($status->value) {
            'confirmed' => 'emails.orders.order-confirmed',
            'in_progress' => 'emails.orders.order-processing',
            'delivered' => 'emails.orders.order-delivered',
            'cancelled' => 'emails.orders.order-cancelled',
            'pending' => 'emails.orders.order-pending',
            'paid' => 'emails.orders.order-paid',
            'failed' => 'emails.orders.order-payment-failed',
            default => 'emails.orders.order-notification',
        };
    }

    /**
     * Get the appropriate subject key based on order status
     */
    private function getSubjectKey(): string
    {
        $status = $this->newStatus ?? $this->order->status;

        return match ($status->value) {
            'confirmed' => 'email.order_confirmed_subject',
            'in_progress' => 'email.order_processing_subject',
            'delivered' => 'email.order_delivered_subject',
            'cancelled' => 'email.order_cancelled_subject',
            'pending' => 'email.order_pending_subject',
            'paid' => 'email.order_paid_subject',
            'failed' => 'email.order_payment_failed_subject',
            default => 'email.order_notification_subject',
        };
    }
}
