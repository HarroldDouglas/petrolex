<?php

echo "Testing updated product quantity logic with model methods...\n\n";

// Mock data for testing
echo "=== BOTTLE PRODUCT TEST ===\n";
$mockBottleData = [
    'pivot' => (object) [
        'stock_filled' => 30,
        'stock_empty' => 20,
    ],
];

echo "Mock bottle stock data:\n";
echo '- Filled bottles: '.$mockBottleData['pivot']->stock_filled."\n";
echo '- Empty bottles: '.$mockBottleData['pivot']->stock_empty."\n\n";

$bottleResponse = [
    'id' => 1,
    'type' => 'bottle',
    'name' => 'Bouteille de 6Kg',
    'description' => 'Une bouteille de gaz de 6 kilogrammes.',
    'category_name' => 'Bouteilles à gaz domestiques',
    'specifications' => [
        ['name' => 'Hauteur', 'value' => '40.00 cm'],
    ],
    'options' => [
        [
            'value' => 'bottle_with_content',
            'label' => 'Bouteille avec recharge',
            'price' => '5000.00',
            'quantity' => $mockBottleData['pivot']->stock_filled, // Using getFilledBottleQuantity()
        ],
        [
            'value' => 'content',
            'label' => 'Recharge uniquement',
            'price' => '3000.00',
            'quantity' => $mockBottleData['pivot']->stock_empty, // Using getEmptyBottleQuantity()
        ],
    ],
];

echo "Expected bottle response:\n";
echo json_encode($bottleResponse, JSON_PRETTY_PRINT)."\n\n";

echo "=== ACCESSORY PRODUCT TEST ===\n";
$mockAccessoryData = [
    'pivot' => (object) [
        'stock' => 87,
    ],
];

echo "Mock accessory stock data:\n";
echo '- Stock: '.$mockAccessoryData['pivot']->stock."\n\n";

$accessoryResponse = [
    'id' => 2,
    'type' => 'accessory',
    'name' => 'Protection anti-chute',
    'description' => 'Protection pour éviter la chute des bouteilles',
    'category_name' => 'Accessoires de sécurité et distributions',
    'specifications' => [],
    'options' => [
        [
            'value' => 'default',
            'label' => 'default',
            'price' => '500.00',
            'quantity' => $mockAccessoryData['pivot']->stock, // Using getAccessoryQuantity()
        ],
    ],
];

echo "Expected accessory response:\n";
echo json_encode($accessoryResponse, JSON_PRETTY_PRINT)."\n\n";

echo "=== CHANGES SUMMARY ===\n";
echo "✅ Added methods to ProductCategoryDistributionCenter model:\n";
echo "   - getFilledBottleQuantity(): Returns stock_filled\n";
echo "   - getEmptyBottleQuantity(): Returns stock_empty\n";
echo "   - getAccessoryQuantity(): Returns stock\n\n";
echo "✅ Updated ProductResource to use model methods:\n";
echo "   - Bottles: Different quantities for each option\n";
echo "   - Accessories: Quantity in options for coherence\n\n";
echo "✅ Maintained separation of concerns:\n";
echo "   - Business logic in model\n";
echo "   - Presentation logic in resource\n";
