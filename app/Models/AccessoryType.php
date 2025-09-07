<?php

namespace App\Models;

use App\Enums\ProductType;
use App\Traits\HasMediaCollections;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;

/**
 * @property int $id
 * @property string $name
 * @property string|null $name_en
 * @property string|null $description
 * @property string|null $description_en
 * @property float $price
 * @property bool $is_active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 *
 * // Relations
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Product> $products
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Accessory> $accessories
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProductCategory> $productCategories
 *
 * // Accessors
 *
 * // Query Scopes
 */
class AccessoryType extends Model implements HasMedia
{
    use HasFactory;
    use HasMediaCollections;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'name_en',
        'price',
        'description',
        'description_en',
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
    public function products(): HasManyThrough
    {
        return $this->hasManyThrough(
            Product::class,
            ProductCategory::class,
            'product_type_id',
            'product_category_id'
        )->where('product_categories.product_type', ProductType::ACCESSORY());
    }

    /**
     * Get all accessories for this accessory type.
     *
     * @return Collection<int, Accessory>
     */
    public function accessories(): Collection
    {
        return $this->products()
            ->whereHas('accessory')
            ->with('accessory')
            ->get()
            ->map(fn (Product $product) => $product->accessory)
            ->filter();
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
            ->when($distributionCenterIds, function (\Illuminate\Database\Eloquent\Builder $query) use ($distributionCenterIds): void {
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

    /**
     * Get the localized name based on the current locale
     */
    public function getLocalizedName(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();

        return match ($locale) {
            'en' => $this->name_en ?: $this->name,
            default => $this->name,
        };
    }

    /**
     * Get the localized description based on the current locale
     */
    public function getLocalizedDescription(?string $locale = null): ?string
    {
        $locale = $locale ?: app()->getLocale();

        return match ($locale) {
            'en' => $this->description_en ?: $this->description,
            default => $this->description,
        };
    }
}
