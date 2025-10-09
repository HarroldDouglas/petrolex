<?php

// Test PaymentMethod with Laravel translations
use App\Enums\PaymentMethod;

echo "=== Testing PaymentMethod with Laravel Translations ===\n\n";

$methods = [
    PaymentMethod::ORANGE_MONEY(),
    PaymentMethod::MTN_MONEY(),
    PaymentMethod::CREDIT_CARD(),
];

echo "1. Testing with French locale:\n";
app()->setLocale('fr');
foreach ($methods as $method) {
    echo "   Method: {$method->value}\n";
    echo "   Label: {$method->label}\n";
    echo "   Validation Text: " . substr($method->validationText() ?? 'null', 0, 60) . "...\n\n";
}

echo "2. Testing with English locale:\n";
app()->setLocale('en');
foreach ($methods as $method) {
    echo "   Method: {$method->value}\n";
    echo "   Label: {$method->label}\n";
    echo "   Validation Text: " . substr($method->validationText() ?? 'null', 0, 60) . "...\n\n";
}

echo "=== Test completed successfully! ===\n";