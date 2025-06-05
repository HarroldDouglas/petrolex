<?php

namespace App\Models;

use App\Enums\BottleMovementType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BottleMovement extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'bottle_id',
        'supplier_delivery_id',
        'distribution_center_id',
        'delivery_person_id',
        'customer_id',
        'order_id',
        'user_id',
        'type',
        'declared_by_customer',
        'notes',
        'movement_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'declared_by_customer' => 'boolean',
        'movement_date' => 'datetime',
        'type' => BottleMovementType::class,
    ];

    /**
     * Get the bottle associated with this movement.
     */
    public function bottle(): BelongsTo
    {
        return $this->belongsTo(Bottle::class);
    }

    /**
     * Get the supplier delivery associated with this movement.
     */
    public function supplierDelivery(): BelongsTo
    {
        return $this->belongsTo(SupplierDelivery::class);
    }

    /**
     * Get the distribution center associated with this movement.
     */
    public function distributionCenter(): BelongsTo
    {
        return $this->belongsTo(DistributionCenter::class);
    }

    /**
     * Get the delivery person associated with this movement.
     */
    public function deliveryPerson(): BelongsTo
    {
        return $this->belongsTo(DeliveryPerson::class);
    }

    /**
     * Get the customer associated with this movement.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the order associated with this movement.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the user who recorded this movement.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
