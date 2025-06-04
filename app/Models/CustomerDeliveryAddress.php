<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerDeliveryAddress extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'customer_id',
        'label',
        'address',
        'latitude',
        'longitude',
        'phone',
        'contact_name',
        'is_default',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_default' => 'boolean',
    ];

    /**
     * Get the customer that owns the address.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the orders using this address.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'delivery_address_id');
    }
}
