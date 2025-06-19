<?php

namespace App\Models;

use App\Enums\ProductType;
use App\Enums\SupplierDeliveryBottleMovementType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * SupplierDeliveryProductType model represents a product type in a supplier delivery.
 *
 * @property int $id
 * @property int $supplier_delivery_id
 * @property ProductType $product_type
 * @property int|null $bottle_type_id
 * @property int|null $accessory_type_id
 * @property int $expected_quantity
 * @property int $bottles_out_quantity
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\AccessoryType|null $accessoryType
 * @property-read \App\Models\BottleType|null $bottleType
 * @property-read \App\Models\SupplierDelivery $supplierDelivery
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SupplierDeliveryBottle> $deliveryBottles
 * @property-read int|null $incoming_scanned_count
 * @property-read int|null $outgoing_scanned_count
 * @property-read bool $incoming_done
 */
class SupplierDeliveryProductType extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'supplier_delivery_id',
        'product_type',
        'bottle_type_id',
        'accessory_type_id',
        'expected_quantity',
        'bottles_out_quantity',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'expected_quantity' => 'integer',
        'bottles_out_quantity' => 'integer',
        'product_type' => ProductType::class,
    ];

    /**
     * Get the supplier delivery this product type belongs to.
     */
    public function supplierDelivery(): BelongsTo
    {
        return $this->belongsTo(SupplierDelivery::class);
    }

    /**
     * Get the bottle type for this product type.
     */
    public function bottleType(): BelongsTo
    {
        return $this->belongsTo(BottleType::class);
    }

    /**
     * Get the accessory type for this product type.
     */
    public function accessoryType(): BelongsTo
    {
        return $this->belongsTo(AccessoryType::class);
    }

    /**
     * Get the bottles for this delivery product type.
     */
    public function deliveryBottles(): HasMany
    {
        return $this->hasMany(SupplierDeliveryBottle::class);
    }

    /**
     * Get only incoming bottles for this delivery product type.
     */
    public function incomingBottles(): HasMany
    {
        return $this->deliveryBottles()->where('movement_type', SupplierDeliveryBottleMovementType::INCOMING()->value);
    }

    /**
     * Get only outgoing bottles for this delivery product type.
     */
    public function outgoingBottles(): HasMany
    {
        return $this->deliveryBottles()->where('movement_type', SupplierDeliveryBottleMovementType::OUTGOING()->value);
    }

    /**
     * Count the number of incoming bottles.
     */
    public function getIncomingScannedCountAttribute(): int
    {
        return $this->incomingBottles()->count();
    }

    /**
     * Count the number of outgoing bottles.
     */
    public function getOutgoingScannedCountAttribute(): int
    {
        return $this->outgoingBottles()->count();
    }

    /**
     * Check if all expected incoming bottles have been scanned.
     */
    public function getIncomingDoneAttribute(): bool
    {
        return $this->incoming_scanned_count >= $this->expected_quantity;
    }

    /**
     * Check if all expected outgoing bottles have been scanned.
     */
    public function getOutgoingDoneAttribute(): bool
    {
        return $this->outgoing_scanned_count >= $this->bottles_out_quantity;
    }

    /**
     * Scope a query to only include bottle product types.
     */
    public function scopeBottles($query)
    {
        return $query->where('product_type', ProductType::BOTTLE());
    }

    /**
     * Scope a query to only include accessory product types.
     */
    public function scopeAccessories($query)
    {
        return $query->where('product_type', ProductType::ACCESSORY());
    }
}
