<?php

namespace App\Models;

use App\Enums\ProductType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $product_category_id
 * @property int $distribution_center_id
 * @property int $stock
 * @property int $stock_empty
 * @property int $stock_filled
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * // Relations
 * @property-read ProductCategory $productCategory
 * @property-read DistributionCenter $distributionCenter
 *
 * // Accessors
 * @property-read int $total_stock
 *
 * // Query Scopes
 */
class ProductCategoryDistributionCenter extends Pivot
{
    protected $table = 'product_category_distribution_center';

    protected $fillable = [
        'product_category_id',
        'distribution_center_id',
        'stock',
        'stock_empty',
        'stock_filled',
    ];

    protected $casts = [
        'stock' => 'integer',
        'stock_empty' => 'integer',
        'stock_filled' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function productCategory(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function distributionCenter(): BelongsTo
    {
        return $this->belongsTo(DistributionCenter::class);
    }

    /**
     * Get total stock based on product type
     * For accessories: just stock
     * For bottles: stock_empty + stock_filled
     */
    public function getTotalStockAttribute(): int
    {
        return match ($this->productCategory->product_type) {
            ProductType::BOTTLE() => $this->stock_empty + $this->stock_filled,
            ProductType::ACCESSORY() => $this->stock,
            default => $this->stock,
        };
    }

    /**
     * Get available stock
     * For accessories: just stock
     * For bottles: stock_filled
     */
    public function getAvailableStockAttribute(): int
    {
        return match ($this->productCategory->product_type) {
            ProductType::BOTTLE() => $this->stock_filled,
            ProductType::ACCESSORY() => $this->stock,
            default => $this->stock,
        };
    }
}
