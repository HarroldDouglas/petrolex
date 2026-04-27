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
 * @property string $category_name
 * @property int $quantity
 * @property array $specifications
 * @property array<array{value: string, label: string, price: string, is_default: bool}>|null $options
 * @property array<array{url: string, thumb: string, medium: string, large: string, is_default: bool}> $images
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
            'type_label' => $productCategory->product_type->label ?? $productCategory->product_type->value,
            'name' => $productTypeInstance?->getLocalizedName() ?? $productCategory->name,
            'description' => $productTypeInstance?->getLocalizedDescription() ?? null,
            'category_name' => $productCategory->product_type->labelForMobile(),
            'quantity' => $this->getQuantity(),
            'specifications' => $productTypeInstance?->specifications ?? [],
            'images' => $productCategory->getImages(),
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
        $accessoryType = $this->resource->productTypeInstance;
        $decimalPlaces = $this->getUserCountryDecimalPlaces();

        if (! $accessoryType) {
            return ['options' => []];
        }

        return [
            'options' => [
                [
                    'value' => 'default',
                    'label' => 'default',
                    'price' => number_format((float) $accessoryType->price, $decimalPlaces, '.', ''),
                    'is_default' => true,
                ],
            ],
        ];
    }

    private function getBottleDetails(): array
    {
        $bottleType = $this->resource->productTypeInstance;
        $decimalPlaces = $this->getUserCountryDecimalPlaces();

        if (! $bottleType) {
            return ['options' => []];
        }

        return [
            'options' => [
                [
                    'value' => BottleOrderType::RECHARGE()->value,
                    'label' => BottleOrderType::RECHARGE()->getLocalizedLabel(),
                    'price' => number_format((float) $bottleType->content_price, $decimalPlaces, '.', ''),
                    'is_default' => true,
                ],
                [
                    'value' => BottleOrderType::FULL()->value,
                    'label' => BottleOrderType::FULL()->getLocalizedLabel(),
                    'price' => number_format((float) $bottleType->full_price, $decimalPlaces, '.', ''),
                    'is_default' => false,
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

    /**
     * Get the decimal places for the current user's country currency
     */
    private function getUserCountryDecimalPlaces(): int
    {
        $user = request()->user();

        if ($user && $user->country && $user->country->currency) {
            return $user->country->currency->decimalPlaces();
        }

        return config('countries.default_decimal_places');
    }
}
