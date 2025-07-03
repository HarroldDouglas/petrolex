<?php

namespace App\Repositories\Eloquent;

use App\Enums\ProductType;
use App\Models\ProductCategory;
use App\Repositories\Contracts\ProductCategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ProductCategoryRepository extends BaseEloquentRepository implements ProductCategoryRepositoryInterface
{
    public function __construct(ProductCategory $productCategory)
    {
        parent::__construct($productCategory);
    }

    /**
     * Get all active product categories of a specific type
     */
    public function getAllActiveByType(ProductType $type): Collection
    {
        if ($type === ProductType::BOTTLE()) {
            return $this->model
                ->where('product_type', $type)
                ->whereIn('product_type_id', function ($query) {
                    $query->select('id')
                        ->from('bottle_types')
                        ->where('is_active', true);
                })
                ->get();
        }

        if ($type === ProductType::ACCESSORY()) {
            return $this->model
                ->where('product_type', $type)
                ->whereIn('product_type_id', function ($query) {
                    $query->select('id')
                        ->from('accessory_types')
                        ->where('is_active', true);
                })
                ->get();
        }

        // Retourner une collection Eloquent vide au lieu d'une collection Support
        return $this->model->whereRaw('1 = 0')->get();
    }

    /**
     * Find a product category by its ID and check if it matches the expected type
     */
    public function findByIdAndType(int $id, ProductType $expectedType): ?ProductCategory
    {
        /** @var ProductCategory|null $productCategory */
        $productCategory = $this->model->find($id);

        if (! $productCategory || ! $productCategory->product_type->equals($expectedType)) {
            return null;
        }

        return $productCategory;
    }

    /**
     * Get all product categories with their distribution centers
     */
    public function getAllWithDistributionCenters(ProductType $type): Collection
    {
        return $this->model
            ->where('product_type', $type)
            ->with(['distributionCenters.distributionCenter'])
            ->get();
    }

    /**
     * Count product categories by type
     */
    public function countByType(ProductType $type): int
    {
        return $this->model->where('product_type', $type)->count();
    }
}
