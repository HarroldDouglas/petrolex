<?php

namespace App\Repositories\Eloquent;

use App\Models\ProductCategoryCityPrice;
use App\Repositories\Contracts\ProductCategoryCityPriceRepositoryInterface;
use App\Repositories\Eloquent\BaseEloquentRepository;

class ProductCategoryCityPriceRepository extends BaseEloquentRepository implements ProductCategoryCityPriceRepositoryInterface
{
    public function __construct(ProductCategoryCityPrice $model)
    {
        parent::__construct($model);
    }

    public function deleteByProductCategoryId(int $productCategoryId): bool
    {
        return ProductCategoryCityPrice::where('product_category_id', $productCategoryId)->delete() > 0;
    }
}
