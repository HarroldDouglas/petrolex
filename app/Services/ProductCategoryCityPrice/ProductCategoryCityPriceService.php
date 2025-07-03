<?php

namespace App\Services\ProductCategoryCityPrice;

use App\Models\ProductCategoryCityPrice;
use App\Repositories\Contracts\ProductCategoryCityPriceRepositoryInterface;
use App\Services\BaseServiceForEntity;
use Illuminate\Support\Collection;

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

    /**
     * Create multiple city prices at once
     */
    public function insertMany(Collection $items): void
    {
        $this->executeInTransaction(function () use ($items) {
            $this->repository->insertMany($items->toArray());
        });
    }
}
