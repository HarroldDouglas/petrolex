<?php

namespace App\Services\ProductCategoryCityPrice;

use App\Models\ProductCategoryCityPrice;
use App\Repositories\Contracts\ProductCategoryCityPriceRepositoryInterface;
use App\Services\BaseServiceForEntity;

class ProductCategoryCityPriceService extends BaseServiceForEntity
{
    /**
     * Create a new ProductCategoryCityPriceService instance.
     */
    public function __construct(ProductCategoryCityPriceRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    protected function getModel(): string
    {
        return ProductCategoryCityPrice::class;
    }
}
