<?php

use App\Models\Order;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;

// Find order 302
$order = Order::find(302);

if (!$order) {
    echo "Order 302 not found!" . PHP_EOL;
    exit;
}

echo "Before update:" . PHP_EOL;
echo "Order Status: " . $order->status->value . PHP_EOL;
if ($order->payment) {
    echo "Payment Status: " . $order->payment->payment_status->value . PHP_EOL;
} else {
    echo "No payment found for this order" . PHP_EOL;
}

// Update order status to failed
$order->status = OrderStatus::FAILED();
$order->save();

// Update payment status to failed if payment exists
if ($order->payment) {
    $order->payment->payment_status = PaymentStatus::FAILED();
    $order->payment->save();
    echo PHP_EOL . "After update:" . PHP_EOL;
    echo "Order Status: " . $order->status->value . PHP_EOL;
    echo "Payment Status: " . $order->payment->payment_status->value . PHP_EOL;
} else {
    echo PHP_EOL . "After update:" . PHP_EOL;
    echo "Order Status: " . $order->status->value . PHP_EOL;
    echo "No payment to update" . PHP_EOL;
}

echo PHP_EOL . "Order 302 has been updated to FAILED status successfully!" . PHP_EOL;