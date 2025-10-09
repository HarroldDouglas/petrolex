<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Http\Api\Controllers\Payment\InitiatePaymentController;
use App\Http\Api\Requests\Payment\InitiatePaymentRequest;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\User;

echo "=== Testing Payment Retry Integration ===\n\n";

try {
    // Create a test user and customer
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    
    // Create an order
    $order = Order::factory()->create([
        'customer_id' => $customer->id,
        'status' => OrderStatus::PENDING()->value,
        'total_amount' => 1000,
    ]);

    echo "1. Created order #{$order->id} for customer #{$customer->id}\n";
    echo "   Order status: {$order->status}\n";
    echo "   Total amount: {$order->total_amount}\n\n";

    // Simulate a failed payment
    $failedPayment = OrderPayment::create([
        'order_id' => $order->id,
        'payment_method' => PaymentMethod::ORANGE_MONEY()->value,
        'amount_paid' => 0,
        'amount_due' => $order->total_amount,
        'payment_status' => PaymentStatus::FAILED()->value,
        'payment_reference' => 'FAILED_' . uniqid(),
        'payment_notes' => 'Simulated failed payment',
    ]);

    echo "2. Created failed payment:\n";
    echo "   Payment ID: {$failedPayment->id}\n";
    echo "   Status: {$failedPayment->payment_status}\n";
    echo "   Reference: {$failedPayment->payment_reference}\n\n";

    // Test if order can accept new payment
    $order->refresh();
    echo "3. Checking if order can accept retry payment:\n";
    echo "   Can accept payment: " . ($order->canAcceptPayment() ? 'YES' : 'NO') . "\n";
    
    if ($order->canAcceptPayment()) {
        echo "   ✅ Payment retry is ALLOWED\n\n";
        
        // Check current payments count
        $paymentsCount = $order->payments()->count();
        echo "4. Current payments count: {$paymentsCount}\n";
        
        echo "   Payment history:\n";
        foreach ($order->payments as $payment) {
            echo "   - ID: {$payment->id}, Status: {$payment->payment_status}, Ref: {$payment->payment_reference}\n";
        }
        
    } else {
        echo "   ❌ Payment retry is BLOCKED\n\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== Integration test completed! ===\n";