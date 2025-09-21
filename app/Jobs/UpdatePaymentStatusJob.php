<?php

namespace App\Jobs;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\OrderPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdatePaymentStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private int $paymentId
    ) {}

    public function handle(): void
    {
        $payment = OrderPayment::findOrFail($this->paymentId);

        // Simulate successful payment after 1 minute
        $payment->update([
            'payment_status' => PaymentStatus::PAID()->value,
            'payment_date' => now(),
            'amount_paid' => $payment->amount_due,
            'amount_due' => 0,
        ]);

        // Update order status to PAID
        $payment->order->update([
            'status' => OrderStatus::PAID()->value,
            'paid_at' => now(),
        ]);

        // TODO: Remove this simulation when real payment callbacks are implemented
    }
}
