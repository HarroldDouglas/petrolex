<?php

namespace App\Models;

use App\Enums\DeliveryType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

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
        'payment_status',
        'payment_method',
        'subtotal',
        'delivery_fee',
        'total_amount',
        'order_date',
        'delivery_date',
        'comments',
        'center_comments',
        'rating',
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
        'payment_status' => PaymentStatus::class,
        'payment_method' => PaymentMethod::class,
        'delivery_type' => DeliveryType::class,
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
}
