<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BottleType extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'capacity',
        'height',
        'width',
        'radius',
        'content_price',
        'bottle_with_content_price',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'height' => 'decimal:2',
        'width' => 'decimal:2',
        'radius' => 'decimal:2',
        'content_price' => 'decimal:2',
        'bottle_with_content_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the products that use this bottle type.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get the bottles of this type.
     */
    public function bottles(): HasMany
    {
        return $this->hasMany(Bottle::class);
    }

    /**
     * Get the supplier delivery product types for this bottle type.
     */
    public function supplierDeliveryProductTypes(): HasMany
    {
        return $this->hasMany(SupplierDeliveryProductType::class);
    }

    public function distributionCenterStocks(): BelongsToMany
    {
        return $this->belongsToMany(DistributionCenter::class)
            ->withPivot(['stock_empty', 'stock_filled'])
            ->withTimestamps();
    }
}
