<?php

namespace App\Models;

use App\Enums\BottleStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bottle extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'bottle_type_id',
        'distribution_center_id',
        'barcode',
        'is_filled',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_filled' => 'boolean',
        'status' => BottleStatus::class,
    ];

    /**
     * Get the product associated with the bottle.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the bottle type of the bottle.
     */
    public function bottleType(): BelongsTo
    {
        return $this->belongsTo(BottleType::class);
    }

    /**
     * Get the distribution center where the bottle is stored.
     */
    public function distributionCenter(): BelongsTo
    {
        return $this->belongsTo(DistributionCenter::class);
    }

    /**
     * Get the movements of this bottle.
     */
    public function movements(): HasMany
    {
        return $this->hasMany(BottleMovement::class);
    }

    /**
     * Get the supplier delivery bottles for this bottle.
     */
    public function supplierDeliveryBottles(): HasMany
    {
        return $this->hasMany(SupplierDeliveryBottle::class);
    }
}
