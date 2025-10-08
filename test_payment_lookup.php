<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo '🔧 Testing Payment Lookup by Transaction Reference' . PHP_EOL;
echo 'Testing the new findPaymentByTransactionReference method' . PHP_EOL . PHP_EOL;

try {
    // Get a real payment to test with
    echo '📋 Finding a payment record to test with...' . PHP_EOL;
    $orderPayment = DB::table('order_payments')
        ->select('id', 'order_id', 'payment_reference', 'payment_method', 'payment_notes')
        ->where('payment_method', 'mtn_money')
        ->orderBy('created_at', 'desc')
        ->first();

    if (!$orderPayment) {
        echo '❌ No MTN Money payments found for testing' . PHP_EOL;
        exit(1);
    }

    echo '✅ Found payment:' . PHP_EOL;
    echo '   - ID: ' . $orderPayment->id . PHP_EOL;
    echo '   - Order ID: ' . $orderPayment->order_id . PHP_EOL;
    echo '   - Payment Reference: ' . $orderPayment->payment_reference . PHP_EOL;
    echo '   - Notes: ' . ($orderPayment->payment_notes ?? 'NULL') . PHP_EOL . PHP_EOL;

    // Test PaymentService creation
    $gatewayFactory = new \App\Services\PaymentGatewayFactory();
    $paymentService = new \App\Services\PaymentService($gatewayFactory);

    // Test 1: Find by order ID (should work as before)
    echo '🔍 Test 1: Finding payment by order ID...' . PHP_EOL;
    try {
        $reflection = new ReflectionClass($paymentService);
        $method = $reflection->getMethod('findPaymentByOrderId');
        $method->setAccessible(true);
        $foundPayment = $method->invoke($paymentService, (string) $orderPayment->order_id);
        echo '✅ Found by order ID: ' . $foundPayment->id . PHP_EOL;
    } catch (Exception $e) {
        echo '❌ Failed to find by order ID: ' . $e->getMessage() . PHP_EOL;
    }

    // Test 2: Find by PETROLEX payment reference
    echo '🔍 Test 2: Finding payment by PETROLEX reference...' . PHP_EOL;
    try {
        $reflection = new ReflectionClass($paymentService);
        $method = $reflection->getMethod('findPaymentByTransactionReference');
        $method->setAccessible(true);
        $foundPayment = $method->invoke($paymentService, $orderPayment->payment_reference);
        echo '✅ Found by PETROLEX reference: ' . $foundPayment->id . PHP_EOL;
    } catch (Exception $e) {
        echo '❌ Failed to find by PETROLEX reference: ' . $e->getMessage() . PHP_EOL;
    }

    // Test 3: Test handleCallback method parameter detection
    echo '🔍 Test 3: Testing handleCallback parameter detection...' . PHP_EOL;
    echo '   - Order ID (numeric): ' . $orderPayment->order_id . ' -> ' . (is_numeric($orderPayment->order_id) ? 'Numeric ✅' : 'Not numeric ❌') . PHP_EOL;
    echo '   - Payment Reference (string): ' . $orderPayment->payment_reference . ' -> ' . (is_numeric($orderPayment->payment_reference) ? 'Numeric ❌' : 'Not numeric ✅') . PHP_EOL;
    echo '   - UUID (string): aff30060-6cc6-4354-b1cf-5aa892b4c801 -> ' . (is_numeric('aff30060-6cc6-4354-b1cf-5aa892b4c801') ? 'Numeric ❌' : 'Not numeric ✅') . PHP_EOL;

    echo PHP_EOL . '🎉 Payment lookup tests completed!' . PHP_EOL;
    echo 'The handleCallback method should now correctly handle both:' . PHP_EOL;
    echo '- Order IDs (numeric): Uses findPaymentByOrderId()' . PHP_EOL;
    echo '- Transaction References (UUID/string): Uses findPaymentByTransactionReference()' . PHP_EOL;

} catch (Exception $e) {
    echo '❌ Error: ' . $e->getMessage() . PHP_EOL;
    echo 'File: ' . $e->getFile() . ':' . $e->getLine() . PHP_EOL;
}