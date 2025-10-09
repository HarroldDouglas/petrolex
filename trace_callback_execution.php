<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo '🔍 Tracing PaymentService handleCallback Execution' . PHP_EOL;
echo 'Testing if the new logs are actually being executed' . PHP_EOL . PHP_EOL;

try {
    // Find payment 311 from the logs
    $orderPayment = \App\Models\OrderPayment::with(['order'])->find(311);
    
    if (!$orderPayment) {
        echo '❌ OrderPayment 311 not found' . PHP_EOL;
        return;
    }
    
    echo '📋 Testing with OrderPayment 311:' . PHP_EOL;
    echo '   - Payment ID: ' . $orderPayment->id . PHP_EOL;
    echo '   - Order ID: ' . $orderPayment->order_id . PHP_EOL;
    echo '   - Payment Reference: ' . $orderPayment->payment_reference . PHP_EOL;
    echo '   - Payment Status: ' . $orderPayment->payment_status->value . PHP_EOL;
    echo '   - Order Status: ' . $orderPayment->order->status . PHP_EOL . PHP_EOL;
    
    // Create services
    $gatewayFactory = new \App\Services\PaymentGatewayFactory();
    $orderService = new \App\Services\Order\OrderService(
        app()->make(\App\Repositories\Contracts\OrderRepositoryInterface::class),
        app()->make(\App\Repositories\Contracts\BottleRepositoryInterface::class),
        app()->make(\App\Repositories\Contracts\OrderBottleScanRepositoryInterface::class),
        app()->make(\App\Repositories\Contracts\ProductRepositoryInterface::class),
        app()->make(\App\Services\ProductCategoryService::class)
    );
    $paymentService = new \App\Services\PaymentService($gatewayFactory, $orderService);
    
    // Simulate the exact callback data from the logs
    $callbackData = [
        'success' => true,
        'status' => 'SUCCESSFUL',
        'amount' => 10.0,
        'should_retry' => false,
        'transaction_ref' => '83d65c20-2a2a-4f8c-b106-f861d7132a4a',
        'transaction_status' => 'SUCCESSFUL',
        'transaction_amount' => 10.0
    ];
    
    echo '🔍 Testing handleCallback with exact data from logs:' . PHP_EOL;
    echo '   → Order ID: ' . $orderPayment->order_id . PHP_EOL;
    echo '   → Callback Data: ' . json_encode($callbackData, JSON_PRETTY_PRINT) . PHP_EOL;
    
    echo PHP_EOL . '🎯 Calling handleCallback - watch for new log messages...' . PHP_EOL;
    echo '   Expected logs:' . PHP_EOL;
    echo '      - 🔔 Received Payment Callback' . PHP_EOL;
    echo '      - 🔔 Handling Payment Callback' . PHP_EOL;
    echo '      - Processing payment response' . PHP_EOL;
    echo PHP_EOL;
    
    // Call handleCallback and monitor for logs
    $startTime = microtime(true);
    
    try {
        $paymentService->handleCallback((string) $orderPayment->order_id, $callbackData);
        echo '✅ handleCallback completed successfully' . PHP_EOL;
    } catch (Exception $e) {
        echo '❌ handleCallback failed: ' . $e->getMessage() . PHP_EOL;
        echo 'Trace: ' . $e->getTraceAsString() . PHP_EOL;
        return;
    }
    
    $endTime = microtime(true);
    $executionTime = round(($endTime - $startTime) * 1000, 2);
    
    echo '⏱️  Execution time: ' . $executionTime . 'ms' . PHP_EOL . PHP_EOL;
    
    // Check if anything changed
    $orderPayment->refresh();
    $orderPayment->order->refresh();
    
    echo '📋 After handleCallback:' . PHP_EOL;
    echo '   - Payment Status: ' . $orderPayment->payment_status->value . PHP_EOL;
    echo '   - Order Status: ' . $orderPayment->order->status . PHP_EOL . PHP_EOL;
    
    // Now let's manually check the PaymentResponse creation logic
    echo '🔍 Manual PaymentResponse creation test:' . PHP_EOL;
    
    // Test the mapping function
    $reflection = new ReflectionClass($paymentService);
    $mapMethod = $reflection->getMethod('mapTransactionStatusToPaymentStatus');
    $mapMethod->setAccessible(true);
    
    $mappedStatus = $mapMethod->invoke($paymentService, $callbackData['transaction_status']);
    echo '   → mapTransactionStatusToPaymentStatus("' . $callbackData['transaction_status'] . '") = "' . $mappedStatus . '"' . PHP_EOL;
    
    // Test PaymentCallbackData creation
    $callbackDto = new \App\DTOs\PaymentCallbackData(
        transactionReference: $callbackData['transaction_ref'],
        status: $mappedStatus,
        amount: $callbackData['transaction_amount'],
        rawData: $callbackData
    );
    echo '   → PaymentCallbackData status: "' . $callbackDto->status . '"' . PHP_EOL;
    
    // Test PaymentResponse success logic
    $successLogic = ($callbackDto->status === \App\Enums\PaymentStatus::PAID()->value);
    echo '   → PaymentResponse success logic: ' . ($successLogic ? 'true' : 'false') . PHP_EOL;
    echo '   → Comparison: "' . $callbackDto->status . '" === "' . \App\Enums\PaymentStatus::PAID()->value . '"' . PHP_EOL;
    
    if (!$successLogic) {
        echo '   ❌ BUG IDENTIFIED: success should be true but is false!' . PHP_EOL;
        echo '   🔍 Debug info:' . PHP_EOL;
        echo '      - Mapped status type: ' . gettype($callbackDto->status) . PHP_EOL;
        echo '      - PAID() value type: ' . gettype(\App\Enums\PaymentStatus::PAID()->value) . PHP_EOL;
        echo '      - Mapped status length: ' . strlen($callbackDto->status) . PHP_EOL;
        echo '      - PAID() value length: ' . strlen(\App\Enums\PaymentStatus::PAID()->value) . PHP_EOL;
    } else {
        echo '   ✅ PaymentResponse success logic should work correctly' . PHP_EOL;
    }
    
    echo PHP_EOL . '🎯 Issue Analysis:' . PHP_EOL;
    
    if ($executionTime < 5) {
        echo '   ⚠️  Very fast execution - might indicate cached result or early return' . PHP_EOL;
    }
    
    echo '   📊 Log Analysis:' . PHP_EOL;
    echo '      - If new logs (🔔) don\'t appear: Code caching issue or different execution path' . PHP_EOL;
    echo '      - If PaymentResponse still has success=false: Logic bug persists' . PHP_EOL;
    echo '      - If order status not updated: success=false preventing update' . PHP_EOL;

} catch (Exception $e) {
    echo '❌ Error: ' . $e->getMessage() . PHP_EOL;
    echo 'File: ' . $e->getFile() . ':' . $e->getLine() . PHP_EOL;
}