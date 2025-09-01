<?php

namespace App\Models;

use App\Enums\BottleOrderType;
use App\Enums\ProductType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $order_id
 * @property int $product_category_id
 * @property int $quantity
 * @property float $unit_price
 * @property float $total_price
 * @property BottleOrderType|null $bottle_type
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 *
 * // Relations
 * @property-read Order $order
 * @property-read ProductCategory $productCategory
 * @property-read \Illuminate\Database\Eloquent\Collection<int, OrderBottleScans> $orderBottleScans
 * @property-read \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\Bottle, \App\Models\OrderBottleScans> $bottles
 *
 * // Accessors
 * @property-read int $scanned_bottles_count
 * @property-read ProductType $productType
 *
 * // Query Scopes
 */
class OrderItem extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'order_id',
        'product_category_id',
        'quantity',
        'bottle_type',
        'unit_price',
        'total_price',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'bottle_type' => BottleOrderType::class.':nullable',
    ];

    /**
     * Get the order that owns the item.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product category for this item.
     */
    public function productCategory(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function productType(): ProductType
    {
        return $this->productCategory->product_type;
    }

    /**
     * Get the bottle mappings for this order item.
     */
    public function orderBottleScans(): HasMany
    {
        return $this->hasMany(OrderBottleScans::class);
    }

    /**
     * Get the bottles associated with this order item.
     */
    public function bottles(): BelongsToMany
    {
        return $this->belongsToMany(Bottle::class, 'order_bottle_scans')
            ->withTimestamps();
    }

    /**
     * Get the number of bottles scanned for this order item.
     */
    public function getScannedBottlesCountAttribute(): int
    {
        return $this->orderBottleScans()->count();
    }

    // ===== METHODS =====

    /**
     * Check if all bottles have been scanned for this order item.
     */
    public function areAllBottlesScanned(): bool
    {
        if (! $this->isBottle()) {
            return true;
        }

        return $this->scanned_bottles_count >= $this->quantity;
    }

    public function isBottle(): bool
    {
        return $this->productCategory->product_type === ProductType::BOTTLE();
    }

    public function isAccessory(): bool
    {
        return $this->productCategory->product_type === ProductType::ACCESSORY();
    }
}
