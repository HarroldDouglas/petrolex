<?php

namespace App\Models;

use App\Enums\ProductType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
 * @property-read Collection<int, Product> $products
 * @property-read Collection<int, OrderItem> $orderItems
 * @property-read Collection<int, ProductCategoryDistributionCenter> $distributionCenters
 * @property-read Collection<int, ProductCategoryCityPrice> $cityPrices
 *
 * // Accessors
 * @property-read BottleType|AccessoryType|null $productTypeInstance
 * @property-read string $name
 * @property-read bool $is_active
 * @property-read float|null $price
 *
 * // Query Scopes
 *
 * @method static Builder ofType(ProductType $type)
 * @method static Builder active()
 * @method static Builder bottles()
 * @method static Builder accessories()
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

    // ===== RELATIONS =====

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function distributionCenters(): HasMany
    {
        return $this->hasMany(ProductCategoryDistributionCenter::class);
    }

    public function cityPrices(): HasMany
    {
        return $this->hasMany(ProductCategoryCityPrice::class, 'product_category_id', 'id');
    }

    // ===== ACCESSORS =====

    public function getProductTypeInstanceAttribute(): BottleType|AccessoryType|null
    {
        return match ($this->attributes['product_type']) {
            ProductType::BOTTLE()->value => BottleType::find($this->product_type_id),
            ProductType::ACCESSORY()->value => AccessoryType::find($this->product_type_id),
            default => null,
        };
    }

    public function getNameAttribute(): string
    {
        return $this->productTypeInstance?->name ?? 'Unknown Product';
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->productTypeInstance?->is_active ?? false;
    }

    public function getPriceAttribute(): ?float
    {
        return $this->productTypeInstance?->price;
    }

    // ===== QUERY SCOPES =====

    public function scopeOfType(Builder $query, ProductType $type): Builder
    {
        return $query->where('product_type', $type);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereHas('productTypeInstance', function ($q) {
            $q->where('is_active', true);
        });
    }

    public function scopeBottles(Builder $query): Builder
    {
        return $query->where('product_type', ProductType::BOTTLE());
    }

    public function scopeAccessories(Builder $query): Builder
    {
        return $query->where('product_type', ProductType::ACCESSORY());
    }
}
