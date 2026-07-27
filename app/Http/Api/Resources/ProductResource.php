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
     * City used to price the bottle options (admin-defined city prices). Set by
     * the controller via self::collectionForCity(); null falls back to base price.
     */
    public ?int $pricingCityId = null;

    /**
     * Build a resource collection priced for a specific city, so the price
     * displayed matches what the order validation expects for that city.
     */
    public static function collectionForCity($resource, ?int $cityId): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $collection = static::collection($resource);
        $collection->collection->each(function (self $item) use ($cityId): void {
            $item->pricingCityId = $cityId;
        });

        return $collection;
    }

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

        // Show the admin-defined city price for the distribution center being
        // browsed, so the price displayed matches what the order validation
        // expects. Falls back to the base price when no city price is set.
        $cityPrice = $this->pricingCityId !== null
            ? app(\App\Services\ProductCategoryService::class)->findCityPrice($this->resource->id, $this->pricingCityId)
            : null;

        $rechargePrice = (float) ($cityPrice?->content_price ?? $bottleType->content_price);
        $fullPrice = (float) ($cityPrice?->content_with_bottle_price ?? $bottleType->full_price);

        return [
            'options' => [
                [
                    'value' => BottleOrderType::RECHARGE()->value,
                    'label' => BottleOrderType::RECHARGE()->getLocalizedLabel(),
                    'price' => number_format($rechargePrice, $decimalPlaces, '.', ''),
                    'is_default' => true,
                ],
                [
                    'value' => BottleOrderType::FULL()->value,
                    'label' => BottleOrderType::FULL()->getLocalizedLabel(),
                    'price' => number_format($fullPrice, $decimalPlaces, '.', ''),
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
