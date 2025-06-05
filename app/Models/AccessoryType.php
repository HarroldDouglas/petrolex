<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccessoryType extends Model
{
    use HasFactory;

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
     * Get the supplier delivery product types for this accessory type.
     */
    public function supplierDeliveryProductTypes(): HasMany
    {
        return $this->hasMany(SupplierDeliveryProductType::class);
    }

    /**
     * Get stock quantity for a specific accessory type across specified distribution centers
     *
     * @param  array|null  $distributionCenterIds  Array of distribution center IDs to filter by
     * @return int Total quantity across specified centers
     */
    public function getStockForType(?array $distributionCenterIds = null): int
    {
        return $this->accessories()
            ->when($distributionCenterIds, function ($query) use ($distributionCenterIds) {
                $query->whereIn('distribution_center_id', $distributionCenterIds);
            })
            ->sum('quantity');
    }
}
