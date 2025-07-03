<?php

namespace App\Repositories\Contracts;

interface ProductCategoryCityPriceRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Insert multiple city prices at once
     *
     * @param  array  $cityPrices  Array of city price data
     * @return bool Success status
     */
    public function insertMany(array $cityPrices): bool;

    /**
     * Delete city prices by product category ID
     *
     * @param  int  $productCategoryId  The product category ID
     * @return bool Success status
     */
    public function deleteByProductCategoryId(int $productCategoryId): bool;
}
