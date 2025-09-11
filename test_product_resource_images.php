<?php

require 'vendor/autoload.php';

echo "Testing ProductResource with images from seeders...\n\n";

// Test bottle product category
$bottleCategory = App\Models\ProductCategory::where('product_type', App\Enums\ProductType::BOTTLE())->first();

if ($bottleCategory) {
    echo "=== BOTTLE PRODUCT ===\n";
    $resource = new App\Http\Api\Resources\ProductResource($bottleCategory);
    $data = $resource->toArray(new Illuminate\Http\Request);

    echo "Product: {$data['name']}\n";
    echo "Type: {$data['type']}\n";
    echo "Price: {$data['price']}\n";
    echo 'Images count: '.count($data['images'])."\n";

    foreach ($data['images'] as $index => $image) {
        $default = $image['is_default'] ? ' (DEFAULT)' : '';
        echo '  Image '.($index + 1)."{$default}:\n";
        echo "    URL: {$image['url']}\n";
        echo "    Thumb: {$image['thumb']}\n";
        echo "    Medium: {$image['medium']}\n";
        echo "    Large: {$image['large']}\n";
    }
    echo "\n";
}

// Test accessory product category
$accessoryCategory = App\Models\ProductCategory::where('product_type', App\Enums\ProductType::ACCESSORY())->first();

if ($accessoryCategory) {
    echo "=== ACCESSORY PRODUCT ===\n";
    $resource = new App\Http\Api\Resources\ProductResource($accessoryCategory);
    $data = $resource->toArray(new Illuminate\Http\Request);

    echo "Product: {$data['name']}\n";
    echo "Type: {$data['type']}\n";
    echo "Price: {$data['price']}\n";
    echo 'Images count: '.count($data['images'])."\n";

    foreach ($data['images'] as $index => $image) {
        $default = $image['is_default'] ? ' (DEFAULT)' : '';
        echo '  Image '.($index + 1)."{$default}:\n";
        echo "    URL: {$image['url']}\n";
        echo "    Thumb: {$image['thumb']}\n";
        echo "    Medium: {$image['medium']}\n";
        echo "    Large: {$image['large']}\n";
    }
    echo "\n";
}

echo "✅ ProductResource image integration test complete!\n";
