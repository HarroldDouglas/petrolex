<?php

namespace App\Models;

use App\Enums\ProductType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $product_category_id
 * @property ProductCategory $productCategory
 * @property ProductType $product_type
 * @property Bottle|null $bottle
 * @property Accessory|null $accessory
 * @property-read int $order_items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, OrderItem> $orderItems
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 */
class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'product_category_id',
    ];

    /**
     * Get the product category associated with this product.
     */
    public function productCategory(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class);
    }

    /**
     * Get the product type via the product category.
     */
    public function getProductTypeAttribute(): ProductType
    {
        return $this->productCategory->product_type;
    }

    /**
     * Get the bottle associated with this product.
     */
    public function bottle(): HasOne
    {
        return $this->hasOne(Bottle::class);
    }

    /**
     * Get the accessory associated with this product.
     */
    public function accessory(): HasOne
    {
        return $this->hasOne(Accessory::class);
    }

    /**
     * Get the order items that include this product.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function name(): string
    {
        return match ($this->product_type) {
            ProductType::BOTTLE() => $this->productCategory?->name ?? 'Bouteille sans type',
            ProductType::ACCESSORY() => $this->productCategory?->name ?? 'Accessoire sans type',
            default => 'Produit inconnu',
        };
    }

    public function type(): string
    {
        return $this->product_type->label;
    }

    /**
     * Get the price of the product.
     */
    public function price(): string
    {
        return match ($this->product_type) {
            ProductType::BOTTLE() => $this->getBottlePrice(),
            ProductType::ACCESSORY() => $this->getAccessoryPrice(),
            default => '0',
        };
    }

    /**
     * Helper method to get formatted bottle price
     */
    private function getBottlePrice(): string
    {
        /** @var BottleType */
        $bottleType = $this->productCategory->productTypeInstance;

        if (! $bottleType) {
            return '0';
        }

        $contentPrice = (int) $bottleType->content_price;
        $bottleWithContentPrice = (int) $bottleType->bottle_with_content_price;

        return $contentPrice.'-'.$bottleWithContentPrice;
    }

    /**
     * Helper method to get accessory price
     */
    private function getAccessoryPrice(): string
    {
        /** @var AccessoryType */
        $accessoryType = $this->productCategory->productTypeInstance;

        return (string) ($accessoryType?->price ?? '0');
    }

    /**
     * Get the registration date in a formatted way.
     */
    public function registrationDate(): string
    {
        return $this->created_at->format('d/m/Y');
    }
}
