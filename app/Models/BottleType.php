<?php

namespace App\Models;

use App\Enums\ProductType;
use App\Traits\HasMediaCollections;
use App\Traits\HasSpecifications;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;

/**
 * @property int $id
 * @property string $name
 * @property string|null $name_en
 * @property string $description
 * @property string|null $description_en
 * @property float $capacity
 * @property float $height
 * @property float $weight
 * @property float $radius
 * @property float $content_price
 * @property float $full_price
 * @property bool $is_active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 *
 * // Relations
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Product> $products
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Bottle> $bottles
 * @property-read \Illuminate\Database\Eloquent\Collection<int, DistributionCenter> $distributionCenters
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProductCategoryCityPrice> $cityPrices
 * @property-read \App\Models\ProductCategory|null $productCategory
 *
 * // Accessors
 *
 * // Query Scopes
 */
class BottleType extends Model implements HasMedia
{
    use HasFactory;
    use HasMediaCollections;
    use HasSpecifications;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'name_en',
        'description',
        'description_en',
        'capacity',
        'height',
        'weight',
        'radius',
        'content_price',
        'full_price',
        'is_active',
        'specifications',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'height' => 'decimal:2',
        'weight' => 'decimal:2',
        'radius' => 'decimal:2',
        'content_price' => 'decimal:2',
        'full_price' => 'decimal:2',
        'is_active' => 'boolean',
        'specifications' => 'array',
    ];

    /**
     * Get the products that use this bottle type.
     */
    public function products(): HasManyThrough
    {
        return $this->hasManyThrough(
            Product::class,
            ProductCategory::class,
            'product_type_id',
            'product_category_id'
        )->where('product_categories.product_type', ProductType::BOTTLE());
    }

    /**
     * Get the bottles for the bottle type.
     *
     * @return Collection<int, Bottle>
     */
    public function bottles(): Collection
    {
        return $this->products()
            ->whereHas('bottle')
            ->with('bottle')
            ->get()
            ->map(fn (Product $product) => $product->bottle)
            ->filter();
    }

    /**
     * Get the distribution centers that have this bottle type.
     */
    public function distributionCenters(): BelongsToMany
    {
        return $this->belongsToMany(DistributionCenter::class, 'product_category_distribution_center')
            ->withPivot(['stock_empty', 'stock_filled'])
            ->withTimestamps();
    }

    public function cityPrices(): Collection
    {
        return $this->productCategory?->cityPrices;
    }

    /**
     * Get the product category associated with the bottle type.
     */
    public function productCategory(): HasOne
    {
        return $this->hasOne(ProductCategory::class, 'product_type_id')
            ->where('product_type', ProductType::BOTTLE());
    }

    /**
     * Get the image collections that this model uses
     */
    protected function getImageCollections(): array
    {
        return ['images'];
    }

    /**
     * Determine if this model requires a main image
     */
    public function requiresMainImage(): bool
    {
        return false;
    }

    /**
     * Determine if this model supports multiple images
     */
    public function supportsMultipleImages(): bool
    {
        return true;
    }

    /**
     * Get the identifier for the image
     */
    public function getImageIdentifier(): string
    {
        return "bottle-type-{$this->id}";
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

    /**
     * Get default specifications from existing fields.
     */
    protected function getDefaultSpecifications(): array
    {
        return [
            ['name' => 'capacity', 'name_en' => 'capacity', 'value' => (string) $this->capacity, 'unit' => 'L'],
            ['name' => 'height', 'name_en' => 'height', 'value' => (string) $this->height, 'unit' => 'cm'],
            ['name' => 'weight', 'name_en' => 'weight', 'value' => (string) $this->weight, 'unit' => 'kg'],
            ['name' => 'radius', 'name_en' => 'radius', 'value' => (string) $this->radius, 'unit' => 'cm'],
        ];
    }
}
