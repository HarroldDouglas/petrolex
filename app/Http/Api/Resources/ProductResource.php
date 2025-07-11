<?php

namespace App\Http\Api\Resources;

use App\Enums\ProductType;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
        $productTypeInstance = $this->resource->productTypeInstance;

        return [
            'capacity' => $productTypeInstance->capacity,
            'height' => $productTypeInstance->height,
            'weight' => $productTypeInstance->weight,
            'radius' => $productTypeInstance->radius,
            'content_price' => $productTypeInstance->content_price,
            'bottle_with_content_price' => $productTypeInstance->bottle_with_content_price,
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
