<?php
// Tinker script to test VerifyMTNPaymentStatusJob
// Run with: php artisan tinker < test-verify-job.php

echo "🚀 Testing VerifyMTNPaymentStatusJob" . PHP_EOL;
echo "Reference ID: 161499c3-0737-4511-bed3-25c2c992e77d" . PHP_EOL . PHP_EOL;

try {
    // Test 1: Create and dispatch the job
    echo "📝 Test 1: Creating VerifyMTNPaymentStatusJob..." . PHP_EOL;
    $job = new \App\Jobs\VerifyMTNPaymentStatusJob('161499c3-0737-4511-bed3-25c2c992e77d', 1);
    echo "✅ Job created successfully" . PHP_EOL . PHP_EOL;

    // Test 2: Execute the job directly (synchronous)
    echo "⚡ Test 2: Executing job handle() method directly..." . PHP_EOL;
    $job->handle();
    echo "✅ Job executed successfully" . PHP_EOL . PHP_EOL;

    // Test 3: Dispatch the job to queue (asynchronous)
    echo "📤 Test 3: Dispatching job to queue..." . PHP_EOL;
    \App\Jobs\VerifyMTNPaymentStatusJob::dispatch('161499c3-0737-4511-bed3-25c2c992e77d');
    echo "✅ Job dispatched to queue successfully" . PHP_EOL . PHP_EOL;

    echo "🎉 All tests completed! Check the logs for detailed results." . PHP_EOL;

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
    echo "File: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
    echo "Trace:" . PHP_EOL . $e->getTraceAsString() . PHP_EOL;
}