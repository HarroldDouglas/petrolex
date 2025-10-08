<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo '🔧 Testing VerifyPaymentStatusJob' . PHP_EOL;
echo 'Reference ID: 161499c3-0737-4511-bed3-25c2c992e77d' . PHP_EOL;
echo 'Payment Method: MTN_MONEY' . PHP_EOL;
echo 'Timestamp: ' . date('Y-m-d H:i:s') . PHP_EOL . PHP_EOL;

try {
    // Create PaymentService instance
    $gatewayFactory = new \App\Services\PaymentGatewayFactory();
    $paymentService = new \App\Services\PaymentService($gatewayFactory);

    // Test the VerifyPaymentStatusJob
    echo '📝 Creating VerifyPaymentStatusJob...' . PHP_EOL;
    $job = new \App\Jobs\VerifyPaymentStatusJob(
        '161499c3-0737-4511-bed3-25c2c992e77d',
        \App\Enums\PaymentMethod::MTN_MONEY(),
        $paymentService,
        1
    );
    echo '✅ Job created successfully' . PHP_EOL . PHP_EOL;

    // Execute the job directly
    echo '⚡ Executing job handle() method...' . PHP_EOL;
    $job->handle();
    echo '✅ Job handle() completed successfully!' . PHP_EOL . PHP_EOL;

    echo '🎉 Generic payment verification job test completed!' . PHP_EOL;
    echo 'Check the Laravel logs for detailed execution information.' . PHP_EOL;

} catch (Exception $e) {
    echo '❌ Error: ' . $e->getMessage() . PHP_EOL;
    echo 'File: ' . $e->getFile() . ':' . $e->getLine() . PHP_EOL;
    echo 'Trace: ' . $e->getTraceAsString() . PHP_EOL;
}