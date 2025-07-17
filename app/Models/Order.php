<?php

namespace App\Models;

use App\Enums\DeliveryStatus;
use App\Enums\DeliveryType;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ProductType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $customer_id
 * @property int|null $delivery_person_id
 * @property int|null $customer_delivery_address_id
 * @property OrderStatus $status
 * @property DeliveryStatus $delivery_status
 * @property DeliveryType $delivery_type
 * @property float $total_amount
 * @property string|null $note
 * @property Carbon|null $delivered_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property string $order_number
 *
 * // Relations
 * @property-read Customer $customer
 * @property-read CustomerDeliveryAddress $deliveryAddress
 * @property-read DeliveryPerson $deliveryPerson
 * @property-read DistributionCenter $distributionCenter
 * @property-read OrderPayment $payment
 * @property-read \Illuminate\Database\Eloquent\Collection<int, OrderItem> $items
 * @property-read \Illuminate\Database\Eloquent\Collection<int, BottleMovement> $bottleMovements
 *  * @property-read \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\Product, \App\Models\OrderItem> $products
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Refund> $refunds
 *
 * // Accessors
 * @property-read \App\Enums\PaymentStatus|null $payment_status
 * @property-read \App\Enums\PaymentMethod|null $payment_method
 *
 * // Query Scopes
 */
class Order extends Model
{
    use HasFactory;
    use SoftDeletes;
    // TODO take into account tax

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'customer_id',
        'delivery_address_id',
        'delivery_person_id',
        'distribution_center_id',
        'order_number',
        'delivery_type',
        'status',
        'subtotal',
        'delivery_fee',
        'total_amount',
        'order_date',
        'delivery_date',
        'comments',
        'center_comments',
        'rating',
        'confirmed_at',
        'processing_at',
        'cancelled_at',
        'cancelled_by',
        'cancelled_reason',
        'delivered_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'subtotal' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'order_date' => 'datetime',
        'delivery_date' => 'datetime',
        'status' => OrderStatus::class,
        'delivery_type' => DeliveryType::class,
        'confirmed_at' => 'datetime',
        'processing_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    /**
     * Get the customer that owns the order.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the delivery address for the order.
     */
    public function deliveryAddress(): BelongsTo
    {
        return $this->belongsTo(CustomerDeliveryAddress::class, 'delivery_address_id');
    }

    /**
     * Get the delivery person for the order.
     */
    public function deliveryPerson(): BelongsTo
    {
        return $this->belongsTo(DeliveryPerson::class);
    }

    /**
     * Get the distribution center for the order.
     */
    public function distributionCenter(): BelongsTo
    {
        return $this->belongsTo(DistributionCenter::class);
    }

    /**
     * Get the payment information for the order.
     */
    public function payment(): HasOne
    {
        return $this->hasOne(OrderPayment::class);
    }

    /**
     * Get the items for the order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the bottle movements for this order.
     */
    public function bottleMovements(): HasMany
    {
        return $this->hasMany(BottleMovement::class);
    }

    /**
     * Get all products associated with this order through order items.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'order_items')
            ->withPivot(['quantity', 'unit_price', 'total_price'])
            ->withTimestamps();
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $order): void {
            $order->setDefaultValues();
        });
    }

    /**
     * Set default values for empty fields
     */
    private function setDefaultValues(): void
    {
        if (empty($this->order_number)) {
            $this->order_number = self::generateOrderNumber();
        }
    }

    /**
     * Generate a unique order number
     */
    public static function generateOrderNumber(): string
    {
        $prefix = 'CMD';
        $year = now()->format('Y');
        $month = now()->format('m');

        $lastOrder = self::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastOrder && preg_match('/(\d+)$/', $lastOrder->order_number, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('%s-%s%s-%04d', $prefix, $year, $month, $nextNumber);
    }

    /**
     * Assign a delivery person to this order
     *
     * @return $this
     */
    public function assignToDeliveryPerson(int $deliveryPersonId): self
    {
        $this->update([
            'delivery_person_id' => $deliveryPersonId,
            'status' => OrderStatus::PROCESSING(),
        ]);

        return $this;
    }

    /**
     * Check if the customer can leave a rating and comments
     */
    public function canBeRated(): bool
    {
        return in_array($this->status->value, [OrderStatus::DELIVERED()->value, OrderStatus::CANCELLED()->value]);
    }

    /**
     * Check if this order can be cancelled
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status->value, [
            OrderStatus::CONFIRMED()->value,
            OrderStatus::PROCESSING()->value,
        ]);
    }

    /**
     * Check if the delivery person can be changed for this order
     */
    public function canChangeDeliveryPerson(): bool
    {
        return in_array($this->status->value, [
            OrderStatus::CONFIRMED()->value,
            OrderStatus::PROCESSING()->value,
        ]);
    }

    /**
     * Get payment status through the payment relation
     */
    public function getPaymentStatusAttribute(): ?\App\Enums\PaymentStatus
    {
        /** @var \App\Models\OrderPayment|null $payment */
        $payment = $this->payment;

        return $payment ? $payment->payment_status : null;
    }

    /**
     * Get payment method through the payment relation
     */
    public function getPaymentMethodAttribute(): ?\App\Enums\PaymentMethod
    {
        /** @var \App\Models\OrderPayment|null $payment */
        $payment = $this->payment;

        return $payment ? $payment->payment_method : null;
    }

    /**
     * Get all refunds associated with this order.
     */
    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    /**
     * Check if this order has been refunded.
     */
    public function hasRefunds(): bool
    {
        return $this->refunds()->exists();
    }

    /**
     * Get the total amount refunded for this order.
     */
    public function getTotalRefundedAmount(): float
    {
        return $this->refunds()
            ->where('status', PaymentStatus::PAID())
            ->sum('amount');
    }

    /**
     * Check if this order has bottle items that need scanning
     */
    public function hasBottleItems(): bool
    {
        return $this->items()
            ->whereHas('productCategory', function (Builder $query): void {
                $query->where('product_type', ProductType::BOTTLE()->value);
            })
            ->exists();
    }

    /**
     * Check if all bottles for this order have been scanned
     */
    public function areAllBottlesScanned(): bool
    {
        if (! $this->hasBottleItems()) {
            return true;
        }

        /** @var \Illuminate\Database\Eloquent\Collection<int, OrderItem> $bottleItems */
        $bottleItems = $this->items()->whereHas('productCategory', function (Builder $query): void {
            $query->where('product_type', ProductType::BOTTLE()->value);
        })->get();

        foreach ($bottleItems as $item) {
            if (! $item->areAllBottlesScanned()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the percentage of bottles scanned for this order.
     */
    public function getBottleScanProgressAttribute(): int
    {
        if (! $this->hasBottleItems()) {
            return 100;
        }

        $totalExpectedBottles = 0;
        $totalScannedBottles = 0;

        /** @var \Illuminate\Database\Eloquent\Collection<int, OrderItem> $bottleItems */
        $bottleItems = $this->items()->whereHas('productCategory', function (Builder $query): void {
            $query->where('product_type', ProductType::BOTTLE()->value);
        })->get();

        foreach ($bottleItems as $item) {
            $totalExpectedBottles += $item->quantity;
            $totalScannedBottles += $item->scanned_bottles_count;
        }

        if ($totalExpectedBottles === 0) {
            return 100; // No bottles expected, so 100% scanned
        }

        return (int) round(($totalScannedBottles / $totalExpectedBottles) * 100);
    }
}