<?php

namespace App\Repositories\Contracts;

use App\Enums\ProductType;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Collection;

interface ProductCategoryRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get all active product categories of a specific type
     *
     * @param  ProductType  $type  The type of product categories to retrieve
     * @return Collection Collection of product categories
     */
    public function getAllActiveByType(ProductType $type): Collection;

    /**
     * Find a product category by its ID and check if it matches the expected type
     *
     * @param  int  $id  The ID of the product category
     * @param  ProductType  $expectedType  The expected type of the product category
     * @return ProductCategory|null The product category if found and type matches, null otherwise
     */
    public function findByIdAndType(int $id, ProductType $expectedType): ?ProductCategory;

    /**
     * Get all product categories with their distribution centers
     *
     * @param  ProductType  $type  The type of product categories to retrieve
     * @return Collection Collection of product categories with their distribution centers
     */
    public function getAllWithDistributionCenters(ProductType $type): Collection;

    /**
     * Count product categories by type
     *
     * @param  ProductType  $type  The type to count
     * @return int The number of product categories of the given type
     */
    public function countByType(ProductType $type): int;

    /**
     * Summary of availableStock, get number of accessories and filled bottles
     * @param int $productCategoryId
     * @param int $distributionCenterId
     * @return int
     */ 
    public function getAvailableStock(int $productCategoryId, int $distributionCenterId): int;
}
