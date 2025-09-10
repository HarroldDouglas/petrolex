<?php

// Test the specification formatting
$spec = [
    'name' => 'Capacity',
    'name_en' => 'Capacity',
    'value' => '6',
    'unit' => 'kg',
];

// Simulate the logic from HasSpecifications trait
$locale = 'en';
$translatedName = $spec['name'];

if ($locale === 'en' && isset($spec['name_en']) && ! empty($spec['name_en'])) {
    $translatedName = $spec['name_en'];
}

// Combine value and unit into a single field with space
$combinedValue = $spec['value'];
if (isset($spec['unit']) && ! empty($spec['unit'])) {
    $combinedValue .= ' '.$spec['unit'];
}

$result = [
    'name' => $translatedName,
    'value' => $combinedValue,
];

echo 'Result: '.json_encode($result, JSON_PRETTY_PRINT).PHP_EOL;
echo "Value: '".$result['value']."'".PHP_EOL;
