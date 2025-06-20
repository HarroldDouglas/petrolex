<?php

namespace App\Models;

use App\Enums\ProductType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property ProductType $product_type
 * @property int $product_type_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 *
 * // Relations
 * @property-read BottleType|AccessoryType $productType
 * @property-read Collection<int, Product> $products
 * @property-read Collection<int, OrderItem> $orderItems
 * @property-read Collection<int, ProductCategoryDistributionCenter> $distributionCenters
 *
 * // Accessors
 * @property-read string $name
 * @property-read bool $is_active
 * @property-read float|null $price
 */
class ProductCategory extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'product_type',
        'product_type_id',
    ];

    protected $casts = [
        'product_type' => ProductType::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Polymorphic relation to BottleType or AccessoryType
     */
    public function productType(): MorphTo
    {
        return $this->morphTo('product_type', 'product_type', 'product_type_id');
    }

    /**
     * Relation to individual products
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Relation to order items
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Relation to distribution centers (pivot table)
     */
    public function distributionCenters(): HasMany
    {
        return $this->hasMany(ProductCategoryDistributionCenter::class);
    }

    /**
     * Get the product name from the related type
     */
    public function getNameAttribute(): string
    {
        return $this->productType?->name ?? 'Unknown Product';
    }

    /**
     * Check if the product is active
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->productType?->is_active ?? false;
    }

    /**
     * Scope to filter by product type
     */
    public function scopeOfType($query, ProductType $type)
    {
        return $query->where('product_type', $type);
    }

    /**
     * Scope for active products
     */
    public function scopeActive($query)
    {
        return $query->whereHas('productType', function ($q) {
            $q->where('is_active', true);
        });
    }

    /**
     * Scope for bottles
     */
    public function scopeBottles($query)
    {
        return $query->where('product_type', ProductType::BOTTLE());
    }

    /**
     * Scope for accessories
     */
    public function scopeAccessories($query)
    {
        return $query->where('product_type', ProductType::ACCESSORY());
    }
}
