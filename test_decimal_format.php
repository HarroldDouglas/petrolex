<?php

require_once 'vendor/autoload.php';

use App\Models\Geography\Country;

// Test the constant
echo 'Default decimal places: '.Country::DEFAULT_DECIMAL_PLACES.PHP_EOL;

// Test price formatting
$price = 500;
$formattedPrice = number_format((float) $price, Country::DEFAULT_DECIMAL_PLACES, '.', '');
echo 'Formatted price: '.$formattedPrice.PHP_EOL;
echo 'JSON encoded: '.json_encode($formattedPrice).PHP_EOL;

// Test different prices
$testPrices = [500, 500.5, 123.45, 1000.123];
foreach ($testPrices as $testPrice) {
    $formatted = number_format((float) $testPrice, Country::DEFAULT_DECIMAL_PLACES, '.', '');
    echo "Price $testPrice -> JSON: ".json_encode($formatted).PHP_EOL;
}
