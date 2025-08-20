<?php

namespace App\Http\Api\Resources;

use App\Enums\BottleOrderType;
use App\Enums\ProductType;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $type
 * @property string $name
 * @property string|null $description
 * @property int $quantity
 * @property float|null $price
 * @property float|null $capacity
 * @property float|null $height
 * @property float|null $weight
 * @property float|null $radius
 * @property array<array{value: string, label: string, price: float}>|null $options
 */
class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var ProductCategory $productCategory */
        $productCategory = $this->resource;
        $productTypeInstance = $productCategory->productTypeInstance;

        $commonData = [
            'id' => $productCategory->id,
            'type' => $productCategory->product_type->value,
            'name' => $productTypeInstance->name,
            'description' => $productTypeInstance->description,
            'quantity' => $this->getQuantity(),
        ];

        $specificData = match ($productCategory->product_type) {
            ProductType::ACCESSORY() => $this->getAccessoryDetails(),
            ProductType::BOTTLE() => $this->getBottleDetails(),
            default => [],
        };

        return array_merge($commonData, $specificData);
    }

    private function getAccessoryDetails(): array
    {
        return [
            'price' => $this->resource->productTypeInstance->price,
        ];
    }

    private function getBottleDetails(): array
    {
        $bottleType = $this->resource->productTypeInstance;

        return [
            'capacity' => $bottleType->capacity,
            'height' => $bottleType->height,
            'weight' => $bottleType->weight,
            'radius' => $bottleType->radius,
            'options' => [
                [
                    'value' => BottleOrderType::FULL()->value,
                    'label' => BottleOrderType::FULL()->label,
                    'price' => $bottleType->bottle_with_content_price,
                ],
                [
                    'value' => BottleOrderType::RECHARGE()->value,
                    'label' => BottleOrderType::RECHARGE()->label,
                    'price' => $bottleType->content_price,
                ],
            ],
        ];
    }

    private function getQuantity(): int
    {
        /** @var ProductCategory $productCategory */
        $productCategory = $this->resource;

        if ($productCategory->product_type === ProductType::BOTTLE()) {
            return $productCategory->pivot->stock_filled ?? 0;
        }

        return $productCategory->pivot->stock ?? 0;
    }
}
