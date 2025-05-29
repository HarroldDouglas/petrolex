<?php

namespace Database\Factories;

use App\Enums\BottleStatus;
use App\Enums\ProductType;
use App\Models\AccessoryType;
use App\Models\BottleType;
use App\Models\DistributionCenter;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'product_type' => fake()->randomElement(ProductType::values()),
        ];
    }

    /**
     * Create a bottle product with optional bottle attributes
     */
    public function bottle(?int $bottleTypeId = null, ?int $distributionCenterId = null, array $bottleAttributes = []): static
    {
        return $this->state(function (array $attributes) {
            return [
                'product_type' => ProductType::BOTTLE(),
            ];
        })->afterCreating(function (Product $product) use ($bottleTypeId, $distributionCenterId, $bottleAttributes) {
            $bottleType = $bottleTypeId ? BottleType::find($bottleTypeId) : BottleType::inRandomOrder()->first();
            $distributionCenter = $distributionCenterId ?: DistributionCenter::inRandomOrder()->first()->id;

            $defaultAttributes = [
                'product_id' => $product->id,
                'bottle_type_id' => $bottleType->id,
                'distribution_center_id' => $distributionCenter,
                'barcode' => 'BT'.strtoupper(Str::random(8)),
                'is_filled' => fake()->boolean(65),
                'status' => BottleStatus::IN_STOCK(),
            ];

            $product->bottle()->create(array_merge($defaultAttributes, $bottleAttributes));
        });
    }

    public function accessory(?int $accessoryTypeId = null, ?int $distributionCenterId = null, array $accessoryAttributes = []): static
    {
        return $this->state(function (array $attributes) {
            return [
                'product_type' => ProductType::ACCESSORY(),
            ];
        })->afterCreating(function (Product $product) use ($accessoryTypeId, $distributionCenterId, $accessoryAttributes) {
            $accessoryType = $accessoryTypeId ? AccessoryType::find($accessoryTypeId) : AccessoryType::inRandomOrder()->first();
            $distributionCenter = $distributionCenterId ?: DistributionCenter::inRandomOrder()->first()->id;

            $defaultAttributes = [
                'product_id' => $product->id,
                'accessory_type_id' => $accessoryType->id,
                'distribution_center_id' => $distributionCenter,
                'sku' => fake()->unique()->bothify('ACC-####-***'),
                'quantity' => fake()->numberBetween(1, 100),
            ];

            $product->accessory()->create(array_merge($defaultAttributes, $accessoryAttributes));
        });
    }
}
