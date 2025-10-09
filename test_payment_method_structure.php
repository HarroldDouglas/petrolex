<?php

require_once 'vendor/autoload.php';

use App\Enums\PaymentMethod;

echo "=== Testing PaymentMethod Basic Structure ===\n\n";

// Test basic enum functionality without translations
echo "1. Testing basic enum functionality:\n";
$methods = [
    PaymentMethod::ORANGE_MONEY(),
    PaymentMethod::MTN_MONEY(),
    PaymentMethod::CREDIT_CARD(),
];

foreach ($methods as $method) {
    echo "   Method: {$method->value} ({$method->label})\n";
}

echo "\n2. Testing static methods:\n";
echo "   Validation Text Keys:\n";
foreach (PaymentMethod::validationTextKeys() as $value => $key) {
    echo "     $value: $key\n";
}

echo "\n=== Basic structure test completed! ===\n";
echo "Note: USSD code extraction and translation testing require Laravel application context.\n";