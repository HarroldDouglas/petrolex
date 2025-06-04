<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DistributionCenter extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'country',
        'city',
        'neighborhood',
        'address',
        'description',
        'latitude',
        'longitude',
        'phone',
        'email',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user permissions for this center.
     */
    public function users(): HasMany
    {
        return $this->hasMany(UserDistributionCenter::class);
    }

    /**
     * Get the delivery persons assigned to this center.
     */
    public function deliveryPersons(): BelongsToMany
    {
        return $this->belongsToMany(DeliveryPerson::class, 'delivery_person_distribution_center')
            ->withTimestamps();
    }

    /**
     * Get the bottles stored at this center.
     */
    public function bottles(): HasMany
    {
        return $this->hasMany(Bottle::class);
    }

    /**
     * Get the accessories stored at this center.
     */
    public function accessories(): HasMany
    {
        return $this->hasMany(Accessory::class);
    }

    /**
     * Get the orders processed by this center.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the supplier deliveries for this center.
     */
    public function supplierDeliveries(): HasMany
    {
        return $this->hasMany(SupplierDelivery::class);
    }

    /**
     * Get the bottle movements for this center.
     */
    public function bottleMovements(): HasMany
    {
        return $this->hasMany(BottleMovement::class);
    }

    public function bottleTypeStocks(): BelongsToMany
    {
        return $this->belongsToMany(BottleType::class)
            ->withPivot(['stock_empty', 'stock_filled'])
            ->withTimestamps();
    }
}
