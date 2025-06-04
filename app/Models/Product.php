<?php

namespace App\Models;

use App\Enums\ProductType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    use HasFactory;

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

    /**
     * Scope a query to only include bottle products.
     */
    public function scopeBottles($query)
    {
        return $query->where('product_type', ProductType::BOTTLE());
    }

    /**
     * Scope a query to only include accessory products.
     */
    public function scopeAccessories($query)
    {
        return $query->where('product_type', ProductType::ACCESSORY());
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
}
