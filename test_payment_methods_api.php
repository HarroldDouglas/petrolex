<?php

require_once 'vendor/autoload.php';

use App\Enums\PaymentMethod;
use App\Http\Api\Controllers\Payment\GetPaymentMethodsController;
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Payment Methods API Implementation ===\n\n";

// Test the GetPaymentMethodsController API endpoint
echo "1. Testing API endpoint: GET /api/payment-methods\n";

try {
    // Create a mock request to test the controller
    $controller = new GetPaymentMethodsController();
    $response = $controller();
    
    echo "   Response structure:\n";
    echo "   " . json_encode($response->toArray(), JSON_PRETTY_PRINT) . "\n\n";
    
    // Validate the response contains USSD codes and validation text
    $data = $response->toArray()['data'];
    foreach ($data as $method) {
        echo "   Method: {$method['value']}\n";
        echo "     Label: {$method['label']}\n";
        echo "     Validation Text: " . substr($method['validation_text'], 0, 50) . "...\n\n";
    }
    
} catch (Exception $e) {
    echo "   Error: " . $e->getMessage() . "\n";
}