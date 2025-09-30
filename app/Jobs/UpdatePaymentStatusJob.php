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
use Illuminate\Support\Facades\Log;

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

        // TODO: REMOVE IN PRODUCTION - Auto-assign delivery1@test.com for demo purposes only
        // This auto-assignment should be removed before production deployment
        // In production, orders should be manually assigned or use a proper assignment algorithm
        $deliveryUser = \App\Models\User::where('email', 'delivery1@test.com')->first();
        if ($deliveryUser && $deliveryUser->deliveryPerson) {
            $payment->order->assignToDeliveryPerson($deliveryUser->deliveryPerson->id);
            Log::info('Auto-assigned delivery1@test.com for demo after payment', [
                'order_id' => $payment->order->id,
                'order_number' => $payment->order->order_number,
                'delivery_person_id' => $deliveryUser->deliveryPerson->id,
            ]);
        } else {
            Log::warning('Could not auto-assign delivery1@test.com - user not found or no delivery person', [
                'order_id' => $payment->order->id,
                'order_number' => $payment->order->order_number,
            ]);
        }
        // END TODO: REMOVE IN PRODUCTION

        // TODO: Remove this simulation when real payment callbacks are implemented
    }
}
