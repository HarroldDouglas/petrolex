<?php

namespace App\Models;

use App\Enums\ProductType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'bottle_type_id',
        'accessory_type_id',
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
     * Get the bottle type for this product.
     */
    public function bottleType(): BelongsTo
    {
        return $this->belongsTo(BottleType::class);
    }

    /**
     * Get the accessory type for this product.
     */
    public function accessoryType(): BelongsTo
    {
        return $this->belongsTo(AccessoryType::class);
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

    /**
     * Scope a query to only include bottle products.
     */
    public function scopeBottles($query)
    {
        return $query->where('product_type', ProductType::Bottle);
    }

    /**
     * Scope a query to only include accessory products.
     */
    public function scopeAccessories($query)
    {
        return $query->where('product_type', ProductType::Accessory);
    }
}
