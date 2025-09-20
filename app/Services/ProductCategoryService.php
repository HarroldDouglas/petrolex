<?php

namespace App\Services;

use App\Enums\BottleOrderType;
use App\Enums\ProductType;
use App\Models\AccessoryType;
use App\Models\BottleType;
use App\Models\ProductCategory;
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
        ?BottleOrderType $option = null
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
            if ($option?->value === BottleOrderType::FULL()->value) {
                return $productInstance->full_price;
            } elseif ($option?->value === BottleOrderType::RECHARGE()->value) {
                return $productInstance->content_price;
            }
        } elseif ($productCategory->product_type->value === ProductType::ACCESSORY()->value) {
            return $productInstance->price;
        }

        throw new ModelNotFoundException('Price not found for product type.');
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
