<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $customer_id
 * @property string $address
 * @property string $city
 * @property string $country
 * @property string|null $instructions
 * @property bool $is_default
 * @property bool $is_active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 *
 * // Relations
 * @property-read Customer $customer
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Order> $orders
 *
 * // Accessors
 *
 * // Query Scopes
 */
class CustomerDeliveryAddress extends Model
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
        'label',
        'address',
        'neighborhood',
        'city',
        'country',
        'latitude',
        'longitude',
        'phone',
        'contact_firstname',
        'contact_lastname',
        'email',
        'address_precision',
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

    /**
     * Get the full formatted address.
     */
    public function fullAddress(): string
    {
        $parts = [];
        if ($this->address) {
            $parts[] = $this->address;
        }
        if ($this->neighborhood) {
            $parts[] = $this->neighborhood;
        }
        if ($this->city) {
            $parts[] = $this->city;
        }
        if ($this->country) {
            $parts[] = $this->country;
        }

        return implode(', ', $parts);
    }

    public function getContactFullNameAttribute(): string
    {
        return "{$this->contact_firstname} {$this->contact_lastname}";
    }
}
