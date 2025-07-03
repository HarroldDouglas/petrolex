<?php

namespace App\Repositories\Contracts;

interface ProductCategoryCityPriceRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Delete city prices by product category ID
     *
     * @param  int  $productCategoryId  The product category ID
     * @return bool Success status
     */
    public function deleteByProductCategoryId(int $productCategoryId): bool;
}
