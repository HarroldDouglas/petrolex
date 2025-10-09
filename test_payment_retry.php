<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\OrderPayment;

echo "=== Testing Payment Retry Functionality ===\n\n";

// Test 1: Order with no payments should accept payment
echo "1. Testing order with no payments:\n";
$order = Order::factory()->create(['status' => OrderStatus::PENDING()->value]);
echo "   Can accept payment: " . ($order->canAcceptPayment() ? 'YES' : 'NO') . "\n\n";

// Test 2: Order with failed payment should accept new payment
echo "2. Testing order with failed payment:\n";
$failedPayment = OrderPayment::factory()->create([
    'order_id' => $order->id,
    'payment_status' => PaymentStatus::FAILED()->value,
]);
$order->refresh(); // Refresh to get updated relationships
echo "   Can accept payment after failed payment: " . ($order->canAcceptPayment() ? 'YES' : 'NO') . "\n\n";

// Test 3: Order with pending payment should NOT accept new payment
echo "3. Testing order with pending payment:\n";
$order2 = Order::factory()->create(['status' => OrderStatus::PENDING()->value]);
$pendingPayment = OrderPayment::factory()->create([
    'order_id' => $order2->id,
    'payment_status' => PaymentStatus::PENDING()->value,
]);
echo "   Can accept payment with pending payment: " . ($order2->canAcceptPayment() ? 'YES' : 'NO') . "\n\n";

// Test 4: Order with paid payment should NOT accept new payment
echo "4. Testing order with paid payment:\n";
$order3 = Order::factory()->create(['status' => OrderStatus::PENDING()->value]);
$paidPayment = OrderPayment::factory()->create([
    'order_id' => $order3->id,
    'payment_status' => PaymentStatus::PAID()->value,
]);
echo "   Can accept payment with paid payment: " . ($order3->canAcceptPayment() ? 'YES' : 'NO') . "\n\n";

// Test 5: Order with multiple failed payments should still accept payment
echo "5. Testing order with multiple failed payments:\n";
$order4 = Order::factory()->create(['status' => OrderStatus::PENDING()->value]);
OrderPayment::factory()->create([
    'order_id' => $order4->id,
    'payment_status' => PaymentStatus::FAILED()->value,
]);
OrderPayment::factory()->create([
    'order_id' => $order4->id,
    'payment_status' => PaymentStatus::FAILED()->value,
]);
echo "   Can accept payment with multiple failed payments: " . ($order4->canAcceptPayment() ? 'YES' : 'NO') . "\n\n";

echo "=== Test completed! ===\n";