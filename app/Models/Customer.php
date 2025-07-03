<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property float $current_balance
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 *
 * // Relations
 * @property-read User $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, CustomerDeliveryAddress> $deliveryAddresses
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Order> $orders
 * @property-read \Illuminate\Database\Eloquent\Collection<int, BottleMovement> $bottleMovements
 *
 * // Accessors
 * @property-read string $name
 *
 * // Query Scopes
 */
class Customer extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'current_balance',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'current_balance' => 'decimal:2',
    ];

    // ===== RELATIONS =====

    /**
     * Get the user that owns the customer.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the delivery addresses for the customer.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\CustomerDeliveryAddress, $this>
     */
    public function deliveryAddresses(): HasMany
    {
        return $this->hasMany(CustomerDeliveryAddress::class);
    }

    /**
     * Get the orders for the customer.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the bottle movements for this customer.
     */
    public function bottleMovements(): HasMany
    {
        return $this->hasMany(BottleMovement::class);
    }

    // ===== ACCESSORS =====

    /**
     * Get the customer's name from the associated user.
     */
    public function getNameAttribute(): string
    {
        return $this->user->full_name ?? 'N/A';
    }

    /**
     * Get the default delivery address for this customer.
     */
    public function defaultDeliveryAddress(): ?CustomerDeliveryAddress
    {
        return $this->deliveryAddresses()->where('is_default', true)->first();
    }
}
