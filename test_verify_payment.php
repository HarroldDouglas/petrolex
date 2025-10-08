<?php

/**
 * Tinker script to test MTN Money Gateway verifyPayment method
 * 
 * Usage: 
 * php artisan tinker
 * Then run: include 'test_verify_payment.php';
 */

echo "🚀 Testing MTN Money Gateway verifyPayment method\n";
echo "================================================\n\n";

// Test reference ID
$referenceId = '161499c3-0737-4511-bed3-25c2c992e77d';

echo "📋 Test Details:\n";
echo "- Reference ID: {$referenceId}\n";
echo "- Gateway: MTNMoneyGateway\n";
echo "- Method: verifyPayment()\n\n";

try {
    // Create the MTN Money Gateway instance
    echo "🔧 Creating MTNMoneyGateway instance...\n";
    $gateway = new \App\Services\PaymentGateways\MTNMoneyGateway();
    echo "✅ Gateway created successfully\n\n";

    // Test the verifyPayment method
    echo "🔍 Calling verifyPayment() method...\n";
    echo "Reference ID: {$referenceId}\n\n";
    
    $startTime = microtime(true);
    $response = $gateway->verifyPayment($referenceId);
    $endTime = microtime(true);
    
    $executionTime = round(($endTime - $startTime) * 1000, 2); // Convert to milliseconds
    
    echo "⏱️ Execution Time: {$executionTime}ms\n\n";
    
    // Display the response
    echo "📄 PaymentResponse Object:\n";
    echo "========================\n";
    echo "Success: " . ($response->success ? '✅ true' : '❌ false') . "\n";
    echo "Status: {$response->status}\n";
    echo "Transaction Reference: {$response->transactionReference}\n";
    echo "Payment URL: " . ($response->paymentUrl ?? 'null') . "\n";
    echo "Amount: " . ($response->amount ?? 'null') . "\n";
    echo "Error Message: " . ($response->errorMessage ?? 'null') . "\n";
    
    if (!empty($response->gatewayResponse)) {
        echo "\n🔍 Gateway Response Data:\n";
        echo "========================\n";
        if (is_array($response->gatewayResponse)) {
            foreach ($response->gatewayResponse as $key => $value) {
                if (is_array($value)) {
                    echo "{$key}: " . json_encode($value) . "\n";
                } else {
                    echo "{$key}: {$value}\n";
                }
            }
        } else {
            echo json_encode($response->gatewayResponse, JSON_PRETTY_PRINT);
        }
    }
    
    // Test status mapping
    if (isset($response->gatewayResponse['status'])) {
        echo "\n🔄 Status Mapping Test:\n";
        echo "======================\n";
        $originalStatus = $response->gatewayResponse['status'];
        echo "MTN Status: {$originalStatus}\n";
        echo "Mapped Status: {$response->status}\n";
        
        // Test the private method via reflection (for debugging purposes)
        try {
            $reflection = new ReflectionClass($gateway);
            $mapMethod = $reflection->getMethod('mapMTNMoneyStatus');
            $mapMethod->setAccessible(true);
            $mappedStatus = $mapMethod->invoke($gateway, $originalStatus);
            echo "Private method mapMTNMoneyStatus(): {$mappedStatus}\n";
            
            $msgMethod = $reflection->getMethod('getStatusMessage');
            $msgMethod->setAccessible(true);
            $statusMessage = $msgMethod->invoke($gateway, $mappedStatus);
            echo "Status Message: {$statusMessage}\n";
        } catch (Exception $e) {
            echo "Could not test private methods: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n✅ Test completed successfully!\n";
    
    // Additional tests with different reference IDs
    echo "\n🔄 Testing with different scenarios:\n";
    echo "====================================\n";
    
    $testCases = [
        'invalid-ref-123' => 'Invalid Reference ID',
        '' => 'Empty Reference ID',
        'test-pending-456' => 'Test Pending Status'
    ];
    
    foreach ($testCases as $testRef => $description) {
        echo "\n📋 Test: {$description}\n";
        echo "Reference: '{$testRef}'\n";
        try {
            $testResponse = $gateway->verifyPayment($testRef);
            echo "Result: " . ($testResponse->success ? '✅ Success' : '❌ Failed') . "\n";
            echo "Status: {$testResponse->status}\n";
            if ($testResponse->errorMessage) {
                echo "Error: {$testResponse->errorMessage}\n";
            }
        } catch (Exception $e) {
            echo "Exception: {$e->getMessage()}\n";
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Test failed with exception:\n";
    echo "=============================\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
} catch (\Error $e) {
    echo "💥 Test failed with error:\n";
    echo "==========================\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

echo "\n🏁 Test execution finished.\n";