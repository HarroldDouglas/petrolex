<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string|null $email
 * @property string|null $phone_number
 * @property string|null $address
 * @property Carbon|null $email_verified_at
 * @property Carbon|null $phone_verified_at
 * @property Carbon|null $last_login_at
 * @property bool $is_active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 */
class User extends Authenticatable implements HasMedia
{
    use HasApiTokens;
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasRoles;
    use InteractsWithMedia;
    use Notifiable;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'last_name',
        'first_name',
        'email',
        'phone_number',
        'address',
        'password',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get the customer associated with the user.
     */
    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class);
    }

    /**
     * Get the delivery person associated with the user.
     */
    public function deliveryPerson(): HasOne
    {
        return $this->hasOne(DeliveryPerson::class);
    }

    /**
     * Get the distribution centers this user has permission to access.
     */
    public function distributionCenters(): HasMany
    {
        return $this->hasMany(UserDistributionCenter::class);
    }

    public function accessibleDistributionCenters(): BelongsToMany
    {
        return $this->belongsToMany(DistributionCenter::class, 'user_distribution_centers')
            ->withPivot('is_active')
            ->withTimestamps();
    }

    /**
     * Get only active distribution centers
     */
    public function activeDistributionCenters(): BelongsToMany
    {
        return $this->accessibleDistributionCenters()
            ->wherePivot('is_active', true)
            ->orderBy('name');
    }

    /**
     * Get the supplier deliveries managed by this user.
     */
    public function supplierDeliveries(): HasMany
    {
        return $this->hasMany(SupplierDelivery::class);
    }

    /**
     * Get the bottle movements recorded by this user.
     */
    public function bottleMovements(): HasMany
    {
        return $this->hasMany(BottleMovement::class);
    }

    /**
     * Check if the user is a customer.
     */
    public function isCustomer(): bool
    {
        return $this->customer()->exists();
    }

    /**
     * Check if the user is a delivery person.
     */
    public function isDeliveryPerson(): bool
    {
        return $this->deliveryPerson()->exists();
    }

    public function isGlobal()
    {
        $totalDistributionCenters = DistributionCenter::count();

        return $this->distributionCenters()->count() === $totalDistributionCenters;
    }

    public function requiresMainImage(): bool
    {
        return false;
    }

    public function supportsMultipleImages(): bool
    {
        return false;
    }

    public function getImageIdentifier(): string
    {
        return $this->fullame ?? 'User #'.$this->id;
    }
}
