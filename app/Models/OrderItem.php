<?php

namespace App\Models;

use App\Enums\BottleOrderType;
use App\Enums\ProductType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $order_id
 * @property int $product_id
 * @property int $quantity
 * @property float $unit_price
 * @property float $total_price
 * @property BottleOrderType|null $bottle_type
 * @property Order $order
 * @property Product $product
 * @property-read int $scanned_bottles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, OrderItemBottle> $orderItemBottles
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
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
        'product_id',
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

    public function productType(): ProductType
    {
        /** @var Product $product */
        $product = $this->product;

        return $product->product_type;
    }

    /**
     * Get the product for this item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the bottle mappings for this order item.
     */
    public function orderItemBottles(): HasMany
    {
        return $this->hasMany(OrderItemBottle::class);
    }

    /**
     * Get the bottles associated with this order item.
     */
    public function bottles()
    {
        return $this->belongsToMany(Bottle::class, 'order_item_bottles')
            ->withTimestamps();
    }

    /**
     * Get the number of bottles scanned for this order item.
     */
    public function getScannedBottlesCountAttribute()
    {
        return $this->orderItemBottles()->count();
    }

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
        /** @var Product $product */
        $product = $this->product;

        return $product->product_type === ProductType::BOTTLE();
    }

    public function isAccessory(): bool
    {
        /** @var Product $product */
        $product = $this->product;

        return $product->product_type === ProductType::ACCESSORY();
    }
}
