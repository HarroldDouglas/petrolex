<?php

/**
 * Simple tinker commands to test verifyPayment
 * 
 * Copy and paste these commands directly into tinker:
 */

/**
 * Quick Tinker Commands for Testing MTN Payment Gateway
 * 
 * Usage in Laravel Tinker (php artisan tinker):
 * Copy and paste individual commands or run: include 'tinker_commands.php';
 */

echo "🚀 MTN Payment Gateway Tinker Test Commands\n";
echo "===========================================\n\n";

// 1. Basic verifyPayment test with working reference
echo "1️⃣ Testing verifyPayment with valid reference:\n";
$gateway = new \App\Services\PaymentGateways\MTNMoneyGateway();
$response = $gateway->verifyPayment('161499c3-0737-4511-bed3-25c2c992e77d');
echo "✅ Success: " . ($response->success ? 'Yes' : 'No') . "\n";
echo "📊 Status: {$response->status}\n";
echo "💰 Amount: {$response->amount}\n";
echo "🆔 Financial Transaction ID: " . ($response->gatewayResponse['financialTransactionId'] ?? 'N/A') . "\n\n";

// 2. Test PaymentDetailsData DTO creation
echo "2️⃣ Testing PaymentDetailsData DTO:\n";
$mobilePayment = \App\DTOs\PaymentDetailsData::from([
    'phone' => '677123456'
]);
echo "📱 Mobile Payment - Phone: {$mobilePayment->getPhone()}\n";
echo "📝 Is Mobile Payment: " . ($mobilePayment->isMobilePayment() ? 'Yes' : 'No') . "\n\n";

$cardPayment = \App\DTOs\PaymentDetailsData::from([
    'card_number' => '1234567890123456',
    'cvv' => '123',
    'expiry_date' => '12/25',
    'cardholder_name' => 'John Doe'
]);
echo "💳 Card Payment - Is Card Payment: " . ($cardPayment->isCardPayment() ? 'Yes' : 'No') . "\n";
echo "👤 Cardholder: {$cardPayment->getCardDetails()['cardholder_name']}\n\n";

// 3. Test phone number formatting
echo "3️⃣ Testing phone number formatting:\n";
$reflection = new ReflectionClass($gateway);
$formatMethod = $reflection->getMethod('formatPhoneNumber');
$formatMethod->setAccessible(true);

$testNumbers = ['677123456', '237677123456', '+237677123456', '0677123456'];
foreach ($testNumbers as $number) {
    $formatted = $formatMethod->invoke($gateway, $number);
    echo "📞 {$number} → {$formatted}\n";
}
echo "\n";

// 4. Test status mapping methods
echo "4️⃣ Testing status mapping:\n";
$mapMethod = $reflection->getMethod('mapMTNMoneyStatus');
$mapMethod->setAccessible(true);

$statuses = ['SUCCESSFUL', 'PENDING', 'FAILED', 'TIMEOUT', 'UNKNOWN'];
foreach ($statuses as $status) {
    $mapped = $mapMethod->invoke($gateway, $status);
    echo "🔄 MTN '{$status}' → App '{$mapped}'\n";
}
echo "\n";

// 5. Test error handling with invalid reference
echo "5️⃣ Testing error handling:\n";
try {
    $errorResponse = $gateway->verifyPayment('invalid-reference-123');
    echo "❌ Invalid Reference Result:\n";
    echo "   Success: " . ($errorResponse->success ? 'Yes' : 'No') . "\n";
    echo "   Status: {$errorResponse->status}\n";
    echo "   Error: " . ($errorResponse->errorMessage ?? 'None') . "\n";
} catch (Exception $e) {
    echo "⚠️ Exception caught: {$e->getMessage()}\n";
}
echo "\n";

// 6. Test access token retrieval (makes real API call)
echo "6️⃣ Testing access token (Real API Call):\n";
try {
    $tokenMethod = $reflection->getMethod('getAccessToken');
    $tokenMethod->setAccessible(true);
    $token = $tokenMethod->invoke($gateway);
    echo "🔐 Token Retrieved: " . (strlen($token) > 0 ? "✅ Yes (" . strlen($token) . " chars)" : "❌ No") . "\n";
    if (strlen($token) > 20) {
        echo "🔑 Token Preview: " . substr($token, 0, 20) . "...\n";
    }
} catch (Exception $e) {
    echo "❌ Token Error: {$e->getMessage()}\n";
}
echo "\n";

// 7. Job testing (commented out - uncomment to dispatch real job)
echo "7️⃣ Job dispatch test (COMMENTED - Uncomment to run):\n";
echo "// \$job = new \\App\\Jobs\\VerifyMTNPaymentStatusJob('test-ref-123', 1);\n";
echo "// dispatch(\$job->delay(now()->addSeconds(10)));\n";
echo "// echo 'Job dispatched for 10 seconds delay';\n\n";

// 8. Payment initiation test (commented out - makes real API call)
echo "8️⃣ Payment initiation test (COMMENTED - Uncomment to run):\n";
echo "// \$testPayment = \\App\\DTOs\\PaymentDetailsData::from(['phone' => '677123456']);\n";
echo "// \$response = \$gateway->initiatePayment(\$testPayment);\n";
echo "// echo 'Reference: ' . \$response->transactionReference;\n";
echo "// echo 'Payment URL: ' . \$response->paymentUrl;\n\n";

echo "🎉 All tests completed! Use individual commands above in tinker.\n";
echo "💡 Tip: Run 'php artisan tinker' then 'include \"tinker_commands.php\";'\n";