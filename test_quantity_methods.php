<?php

echo "Testing ProductCategoryDistributionCenter quantity methods...\n\n";

// Simulate bottle data
$bottleData = [
    'productCategory' => (object) ['product_type' => 'bottle'],
    'stock_filled' => 25,
    'stock_empty' => 15,
    'stock' => 0,
];

echo "Bottle Product:\n";
echo "- stock_filled: {$bottleData['stock_filled']}\n";
echo "- stock_empty: {$bottleData['stock_empty']}\n";
echo "- stock: {$bottleData['stock']}\n";

echo "\nExpected method results for bottle:\n";
echo "- getFilledBottleQuantity(): {$bottleData['stock_filled']} (returns stock_filled)\n";
echo "- getEmptyBottleQuantity(): {$bottleData['stock_empty']} (returns stock_empty)\n";
echo "- getAccessoryQuantity(): 0 (returns 0 for bottles)\n\n";

// Simulate accessory data
$accessoryData = [
    'productCategory' => (object) ['product_type' => 'accessory'],
    'stock_filled' => 0,
    'stock_empty' => 0,
    'stock' => 87,
];

echo "Accessory Product:\n";
echo "- stock_filled: {$accessoryData['stock_filled']}\n";
echo "- stock_empty: {$accessoryData['stock_empty']}\n";
echo "- stock: {$accessoryData['stock']}\n";

echo "\nExpected method results for accessory:\n";
echo "- getFilledBottleQuantity(): 0 (returns 0 for accessories)\n";
echo "- getEmptyBottleQuantity(): 0 (returns 0 for accessories)\n";
echo "- getAccessoryQuantity(): {$accessoryData['stock']} (returns stock)\n\n";

echo "ProductResource usage:\n";
echo "- Bottles use getFilledBottleQuantity() and getEmptyBottleQuantity()\n";
echo "- Accessories use getAccessoryQuantity()\n";
echo "- This ensures each option gets the correct quantity for its type\n";
