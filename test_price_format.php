<?php

echo 'Testing price formatting:'.PHP_EOL;
echo '500 -> '.json_encode(number_format(500, 2, '.', '')).PHP_EOL;
echo '500.50 -> '.json_encode(number_format(500.50, 2, '.', '')).PHP_EOL;
echo '123.45 -> '.json_encode(number_format(123.45, 2, '.', '')).PHP_EOL;
echo '1000 -> '.json_encode(number_format(1000, 2, '.', '')).PHP_EOL;

// Test the complete structure
$testData = [
    'id' => 6,
    'type' => 'accessory',
    'name' => 'Protection anti-chute',
    'options' => [
        [
            'value' => 'default',
            'label' => 'default',
            'price' => number_format(500, 2, '.', ''),
        ],
    ],
];

echo PHP_EOL.'Complete JSON structure:'.PHP_EOL;
echo json_encode($testData, JSON_PRETTY_PRINT).PHP_EOL;
