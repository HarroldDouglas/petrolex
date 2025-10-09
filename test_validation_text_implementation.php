<?php

require_once 'vendor/autoload.php';

use App\Enums\PaymentMethod;

echo "=== Testing PaymentMethod Validation Text Implementation ===\n\n";

// Test static methods
echo "1. Testing static methods:\n";
$validationTextKeys = PaymentMethod::validationTextKeys();

echo "   Validation Text Keys:\n";
foreach ($validationTextKeys as $value => $key) {
    echo "     $value: $key\n";
}
echo "\n";

// Test individual payment method instances
echo "2. Testing individual payment method instances:\n";
$methods = [
    PaymentMethod::ORANGE_MONEY(),
    PaymentMethod::MTN_MONEY(),
    PaymentMethod::CREDIT_CARD(),
];

foreach ($methods as $method) {
    echo "   Method: {$method->value} ({$method->label})\n";
    echo "   Validation Text: " . substr($method->validationText() ?? 'null', 0, 80) . "...\n";
    echo "\n";
}

// Test validation text for each method
echo "3. Testing validation text for each method:\n";
foreach ($methods as $method) {
    echo "   {$method->value}: " . substr($method->validationText() ?? 'null', 0, 50) . "...\n";
}

echo "\n=== Test completed successfully! ===\n";