<?php

namespace App\Models;

use App\Enums\ProductType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
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
        'product_type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'product_type' => ProductType::class,
    ];

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
            ProductType::BOTTLE() => $this->bottle?->bottleType?->name ?? 'Bouteille sans type',
            ProductType::ACCESSORY() => $this->accessory?->accessoryType?->name ?? 'Accessoire sans type',
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
            ProductType::ACCESSORY() => $this->accessory?->accessoryType?->price ?? '0',
            default => '0',
        };
    }

    /**
     * Helper method to get formatted bottle price
     */
    private function getBottlePrice(): string
    {
        /** @var Bottle */
        $bottle = $this->bottle;
        /** @var BottleType */
        $bottleType = $bottle?->bottleType;

        if (! $bottle || ! $bottleType) {
            return '0';
        }

        $contentPrice = (int) $bottleType->content_price;
        $bottleWithContentPrice = (int) $bottleType->bottle_with_content_price;

        return $contentPrice.'-'.$bottleWithContentPrice;
    }

    /**
     * Get the stock quantity of the product.
     */
    public function stock(): int
    {
        return match ($this->product_type) {
            ProductType::BOTTLE() => $this->bottle?->quantity ?? 0,
            ProductType::ACCESSORY() => $this->accessory?->quantity ?? 0,
            default => 0,
        };
    }

    /**
     * Get the registration date in a formatted way.
     */
    public function registrationDate(): string
    {
        return $this->created_at->format('d/m/Y');
    }
}
