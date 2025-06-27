<?php

namespace App\Models;

use App\Enums\ProductType;
use App\Traits\HasMediaCollections;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property float $price
 * @property bool $is_active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 */
class AccessoryType extends Model implements HasMedia
{
    use HasFactory;
    use SoftDeletes;
    use HasMediaCollections;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'price',
        'description',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the products that use this accessory type.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get the accessories of this type.
     */
    public function accessories(): HasMany
    {
        return $this->hasMany(Accessory::class);
    }

    /**
     * Get the product categories for this accessory type.
     */
    public function productCategories(): HasMany
    {
        return $this->hasMany(ProductCategory::class, 'product_type_id')
            ->where('product_type', ProductType::ACCESSORY());
    }

    /**
     * Get stock quantity for a specific accessory type across specified distribution centers
     *
     * @param  array|null  $distributionCenterIds  Array of distribution center IDs to filter by
     * @return int Total quantity across specified centers
     */
    public function getStockForType(?array $distributionCenterIds = null): int
    {
        return $this->productCategories()
            ->join('product_category_distribution_center as pcdc', 'product_categories.id', '=', 'pcdc.product_category_id')
            ->when($distributionCenterIds, function ($query) use ($distributionCenterIds) {
                $query->whereIn('pcdc.distribution_center_id', $distributionCenterIds);
            })
            ->sum('pcdc.stock');
    }

    public function requiresMainImage(): bool
    {
        return false;
    }

    public function supportsMultipleImages(): bool
    {
        return true;
    }

    public function getImageIdentifier(): string
    {
        return $this->name ?? 'Accessory Type #'.$this->id;
    }
}
