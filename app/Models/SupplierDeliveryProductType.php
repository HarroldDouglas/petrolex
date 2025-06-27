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
 * @property int $product_category_id
 * @property int $expected_quantity
 * @property int $bottles_out_quantity
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 *
 * // Relations
 * @property-read \App\Models\ProductCategory $productCategory
 * @property-read \App\Models\SupplierDelivery $supplierDelivery
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SupplierDeliveryBottle> $deliveryBottles
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SupplierDeliveryBottle> $incomingBottles
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SupplierDeliveryBottle> $outgoingBottles
 *
 * // Accessors
 * @property-read int $incoming_scanned_count
 * @property-read int $outgoing_scanned_count
 * @property-read bool $incoming_done
 * @property-read bool $outgoing_done
 *
 * // Query Scopes
 *
 * @method static \Illuminate\Database\Eloquent\Builder bottles()
 * @method static \Illuminate\Database\Eloquent\Builder accessories()
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
        'product_category_id',
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
    ];

    /**
     * Get the supplier delivery this product type belongs to.
     */
    public function supplierDelivery(): BelongsTo
    {
        return $this->belongsTo(SupplierDelivery::class);
    }

    /**
     * Get the product category for this product type.
     */
    public function productCategory(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class);
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
    public function scopeBottles(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereHas('productCategory', function (\Illuminate\Database\Eloquent\Builder $q): void {
            $q->where('product_type', ProductType::BOTTLE());
        });
    }

    /**
     * Scope a query to only include accessory product types.
     */
    public function scopeAccessories(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereHas('productCategory', function (\Illuminate\Database\Eloquent\Builder $q): void {
            $q->where('product_type', ProductType::ACCESSORY());
        });
    }
}
