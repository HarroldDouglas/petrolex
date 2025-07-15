<?php

namespace App\Services;

use App\Enums\BottleOrderType;
use App\Enums\ProductType;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\ProductCategory;
use App\Models\ProductCategoryDistributionCenter;

class ProductCategoryService
{
    public function getProductCategory(int $productCategoryId): ProductCategory
    {
        $productCategory = ProductCategory::find($productCategoryId);

        if (! $productCategory) {
            throw new ModelNotFoundException('Product category not found.');
        }

        return $productCategory;
    }

    public function getProductInstance(ProductCategory $productCategory)
    {
        $productInstance = $productCategory->getProductTypeInstanceAttribute();

        if (! $productInstance) {
            throw new ModelNotFoundException('Product instance not found for category.');
        }

        return $productInstance;
    }

    public function getProductPrice(
        ProductCategory $productCategory,
        $productInstance,
        ?string $option = null
    ): float {
        if ($productCategory->product_type === ProductType::BOTTLE()->value) {
            if ($option === BottleOrderType::FULL()->value) {
                return $productInstance->bottle_with_content_price;
            } elseif ($option === BottleOrderType::RECHARGE()->value) {
                return $productInstance->content_price;
            }
        } elseif ($productCategory->product_type === ProductType::ACCESSORY()->value) {
            return $productInstance->price;
        }

        throw new ModelNotFoundException('Price not found for product type.');
    }

    public function getProductQuantity(
        ProductCategory $productCategory,
        int $distributionCenterId
    ): int {
        $productCategoryDistributionCenter = ProductCategoryDistributionCenter::where('product_category_id', $productCategory->id)
            ->where('distribution_center_id', $distributionCenterId)
            ->first();

        return $productCategoryDistributionCenter ? $productCategoryDistributionCenter->total_stock : 0;
    }
}
