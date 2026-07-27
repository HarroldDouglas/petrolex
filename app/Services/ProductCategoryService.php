<?php

namespace App\Services;

use App\Enums\BottleOrderType;
use App\Enums\ProductType;
use App\Models\AccessoryType;
use App\Models\BottleType;
use App\Models\ProductCategory;
use App\Models\ProductCategoryCityPrice;
use App\Repositories\Contracts\ProductCategoryRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProductCategoryService extends BaseServiceForEntity
{
    public function __construct(ProductCategoryRepositoryInterface $repository)
    {
        parent::__construct($repository);
        $this->repository = $repository;
    }

    protected function getModel(): string
    {
        return ProductCategory::class;
    }

    public function getProductPrice(
        int $productCategoryId,
        ?BottleOrderType $option = null,
        ?int $cityId = null
    ): float {
        /** @var ProductCategory $productCategory */
        $productCategory = $this->repository->find($productCategoryId);

        if (! $productCategory) {
            throw new ModelNotFoundException('Product category not found.');
        }

        /**
         * @var BottleType|AccessoryType|null $productInstance
         */
        $productInstance = $productCategory->productTypeInstance;

        if (! $productInstance) {
            throw new ModelNotFoundException('Product instance not found for category.');
        }

        if ($productCategory->product_type->value === ProductType::BOTTLE()->value) {
            // City-specific price defined by admins takes precedence over the
            // base price when a city is provided and a row exists for it.
            $cityPrice = $cityId !== null
                ? $this->findCityPrice($productCategoryId, $cityId)
                : null;

            if ($option?->value === BottleOrderType::FULL()->value) {
                return (float) ($cityPrice?->content_with_bottle_price ?? $productInstance->full_price);
            } elseif ($option?->value === BottleOrderType::RECHARGE()->value) {
                return (float) ($cityPrice?->content_price ?? $productInstance->content_price);
            }
        } elseif ($productCategory->product_type->value === ProductType::ACCESSORY()->value) {
            return $productInstance->price;
        }

        throw new ModelNotFoundException('Price not found for product type.');
    }

    /**
     * Resolve the admin-defined city price row for a bottle category, if any.
     */
    public function findCityPrice(int $productCategoryId, int $cityId): ?ProductCategoryCityPrice
    {
        return ProductCategoryCityPrice::query()
            ->where('product_category_id', $productCategoryId)
            ->where('city_id', $cityId)
            ->first();
    }

    /**
     * Resolve the city id (via neighborhood → municipality → city) of a
     * distribution center, used to price an order in that center's city.
     */
    public function resolveCityIdForDistributionCenter(?int $distributionCenterId): ?int
    {
        if ($distributionCenterId === null) {
            return null;
        }

        return \App\Models\DistributionCenter::with('neighborhood.municipality')
            ->find($distributionCenterId)?->city?->id;
    }

    public function getProductQuantity(
        int $productCategoryId,
        int $distributionCenterId
    ): int {
        /** @var ProductCategory $productCategory */
        $productCategory = $this->repository->find($productCategoryId);

        if (! $productCategory) {
            throw new ModelNotFoundException('Product category not found.');
        }

        /** @var \App\Repositories\Contracts\ProductCategoryRepositoryInterface $repository */
        $repository = $this->repository;
        $availableStock = $repository->getAvailableStock($productCategoryId, $distributionCenterId);

        return $availableStock;
    }
}
