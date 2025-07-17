<?php

namespace App\Services;

use App\Enums\BottleOrderType;
use App\Enums\ProductType;
use App\Models\AccessoryType;
use App\Models\BottleType;
use App\Models\ProductCategory;
use App\Models\ProductCategoryDistributionCenter;
use App\Repositories\Contracts\ProductCategoryRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProductCategoryService extends BaseServiceForEntity
{
    public function __construct(ProductCategoryRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    protected function getModel(): string
    {
        return ProductCategory::class;
    }

    public function getProductPrice(
        ProductCategory $productCategory,
        ?string $option = null
    ): float {
        /**
         * @var BottleType|AccessoryType|null $productInstance
         */
        $productInstance = $productCategory->productTypeInstance;

        if (! $productInstance) {
            throw new ModelNotFoundException('Product instance not found for category.');
        }

        if ($productCategory->product_type->value === ProductType::BOTTLE()->value) {
            if ($option === BottleOrderType::FULL()->value) {
                return $productInstance->bottle_with_content_price;
            } elseif ($option === BottleOrderType::RECHARGE()->value) {
                return $productInstance->content_price;
            }
        } elseif ($productCategory->product_type->value === ProductType::ACCESSORY()->value) {
            return $productInstance->price;
        }

        throw new ModelNotFoundException('Price not found for product type.');
    }

    public function getProductQuantity(
        ProductCategory $productCategory,
        int $distributionCenterId
    ): int {
        // TODO Optimize this method to avoid unnecessary queries
        $productCategoryDistributionCenter = ProductCategoryDistributionCenter::where('product_category_id', $productCategory->id)
            ->where('distribution_center_id', $distributionCenterId)
            ->first();

        return $productCategoryDistributionCenter ? $productCategoryDistributionCenter->total_stock : 0;
    }
}
