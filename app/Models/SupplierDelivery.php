<?php

namespace App\Models;

use App\Enums\SupplierDeliveryStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplierDelivery extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'distribution_center_id',
        'user_id',
        'delivery_number',
        'supplier_name',
        'description',
        'delivery_date',
        'status',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'delivery_date' => 'date',
        'status' => SupplierDeliveryStatus::class,
    ];

    /**
     * Get the distribution center for this delivery.
     */
    public function distributionCenter(): BelongsTo
    {
        return $this->belongsTo(DistributionCenter::class);
    }

    /**
     * Get the user who created this delivery.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the product types included in this delivery.
     */
    public function productTypes(): HasMany
    {
        return $this->hasMany(SupplierDeliveryProductType::class);
    }

    /**
     * Get the bottles included in this delivery.
     */
    public function bottles(): HasMany
    {
        return $this->hasMany(SupplierDeliveryBottle::class);
    }

    /**
     * Get the bottle movements for this delivery.
     */
    public function bottleMovements(): HasMany
    {
        return $this->hasMany(BottleMovement::class);
    }
}
