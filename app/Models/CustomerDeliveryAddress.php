<?php

namespace App\Models;

use App\Models\Geography\City;
use App\Models\Geography\Country;
use App\Models\Geography\Neighborhood;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $customer_id
 * @property int|null $neighborhood_id
 * @property string $address
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
 * @property-read Neighborhood|null $neighborhood
 * @property-read City|null $city
 * @property-read Country|null $country
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
        'neighborhood_id',
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
     * Get the neighborhood that owns the delivery address.
     */
    public function neighborhood(): BelongsTo
    {
        return $this->belongsTo(Neighborhood::class);
    }

    /**
     * Get the city of the delivery address through its neighborhood.
     */
    public function getCityAttribute(): ?City
    {
        return $this->neighborhood->municipality->city ?? null;
    }

    /**
     * Get the country of the delivery address through its city.
     */
    public function getCountryAttribute(): ?Country
    {
        return $this->neighborhood->municipality->city->country ?? null;
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
            $parts[] = $this->neighborhood->name;
        }
        if ($this->city) {
            $parts[] = $this->city->name;
        }
        if ($this->country) {
            $parts[] = $this->country->name;
        }

        return implode(', ', $parts);
    }
}
