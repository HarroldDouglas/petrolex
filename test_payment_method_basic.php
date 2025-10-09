<?php

require_once 'vendor/autoload.php';

use App\Enums\PaymentMethod;

echo "=== Testing PaymentMethod with Dynamic Translations ===\n\n";

// Test basic enum functionality
echo "1. Testing basic enum functionality:\n";
$methods = [
    PaymentMethod::ORANGE_MONEY(),
    PaymentMethod::MTN_MONEY(),
    PaymentMethod::CREDIT_CARD(),
];

foreach ($methods as $method) {
    echo "   Method: {$method->value} ({$method->label})\n";
    echo "   Validation Text: " . substr($method->validationText() ?? 'null', 0, 50) . "...\n";
}

echo "\n2. Testing static methods:\n";
echo "   Validation Text Keys:\n";
foreach (PaymentMethod::validationTextKeys() as $value => $key) {
    echo "     $value: $key\n";
}

echo "\n=== Basic functionality test completed! ===\n";
echo "Note: Translation testing requires Laravel application context.\n";