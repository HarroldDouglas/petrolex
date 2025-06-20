<?php

namespace App\Models;

use App\Enums\DeliveryStatus;
use App\Enums\DeliveryType;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ProductType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
 */
class Order extends Model
{
    use HasFactory;
    use SoftDeletes;

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
    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_items')
            ->withPivot(['quantity', 'unit_price', 'total_price'])
            ->withTimestamps();
    }

    /**
     * Assign a delivery person to this order
     *
     * @return $this
     */
    public function assignToDeliveryPerson(int $deliveryPersonId)
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
        return in_array($this->status, [OrderStatus::DELIVERED(), OrderStatus::CANCELLED()]);
    }

    /**
     * Check if this order can be cancelled
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, [
            OrderStatus::CONFIRMED(),
            OrderStatus::PROCESSING(),
        ]);
    }

    /**
     * Check if the delivery person can be changed for this order
     */
    public function canChangeDeliveryPerson(): bool
    {
        return in_array($this->status, [
            OrderStatus::CONFIRMED(),
            OrderStatus::PROCESSING(),
        ]);
    }

    /**
     * Get payment status through the payment relation
     *
     * @return mixed
     */
    public function getPaymentStatusAttribute()
    {
        /** @var \App\Models\OrderPayment|null $payment */
        $payment = $this->payment;

        return $payment ? $payment->payment_status : null;
    }

    /**
     * Get payment method through the payment relation
     *
     * @return mixed
     */
    public function getPaymentMethodAttribute()
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
            ->whereHas('product', function ($query) {
                $query->where('product_type', ProductType::BOTTLE());
            })
            ->exists();
    }

    /**
     * Check if this order is eligible for bottle scanning
     */
    public function canScanBottles(): bool
    {
        return $this->status === OrderStatus::CONFIRMED() && $this->hasBottleItems();
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
        $bottleItems = $this->items()->whereHas('product', function ($query) {
            $query->where('product_type', ProductType::BOTTLE());
        })->get();

        foreach ($bottleItems as $item) {
            if (! $item->areAllBottlesScanned()) {
                return false;
            }
        }

        return true;
    }
}
