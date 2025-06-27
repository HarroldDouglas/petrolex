<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property string $name
 * @property string $address
 * @property string $city
 * @property string $country
 * @property string|null $phone
 * @property string|null $email
 * @property bool $is_active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 *
 * // Relations
 * @property-read \Illuminate\Database\Eloquent\Collection<int, UserDistributionCenter> $users
 * @property-read \Illuminate\Database\Eloquent\Collection<int, DeliveryPerson> $deliveryPersons
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Bottle> $bottles
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Accessory> $accessories
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Order> $orders
 * @property-read \Illuminate\Database\Eloquent\Collection<int, SupplierDelivery> $supplierDeliveries
 * @property-read \Illuminate\Database\Eloquent\Collection<int, BottleMovement> $bottleMovements
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProductCategory> $productCategories
 * @property-read \Illuminate\Database\Eloquent\Collection<int, BottleType> $bottleTypeStocks
 *
 * // Accessors
 * @property-read int $total_empty_bottles
 * @property-read int $total_filled_bottles
 * @property-read int $total_bottles
 *
 * // Query Scopes
 */
class DistributionCenter extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'country',
        'city',
        'neighborhood',
        'address',
        'description',
        'latitude',
        'longitude',
        'phone',
        'email',
        'is_active',
        'storage_capacity',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user permissions for this center.
     */
    public function users(): HasMany
    {
        return $this->hasMany(UserDistributionCenter::class);
    }

    /**
     * Get the delivery persons assigned to this center.
     */
    public function deliveryPersons(): BelongsToMany
    {
        return $this->belongsToMany(DeliveryPerson::class, 'delivery_person_distribution_center')
            ->withTimestamps();
    }

    /**
     * Get the bottles stored at this center.
     */
    public function bottles(): HasMany
    {
        return $this->hasMany(Bottle::class);
    }

    /**
     * Get the accessories stored at this center.
     */
    public function accessories(): HasMany
    {
        return $this->hasMany(Accessory::class);
    }

    /**
     * Get the orders processed by this center.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the supplier deliveries for this center.
     */
    public function supplierDeliveries(): HasMany
    {
        return $this->hasMany(SupplierDelivery::class);
    }

    /**
     * Get the bottle movements for this center.
     */
    public function bottleMovements(): HasMany
    {
        return $this->hasMany(BottleMovement::class);
    }

    /**
     * Get the product categories for this distribution center.
     */
    public function productCategories(): BelongsToMany
    {
        return $this->belongsToMany(ProductCategory::class, 'product_category_distribution_center')
            ->using(ProductCategoryDistributionCenter::class)
            ->withPivot(['stock_empty', 'stock_filled'])
            ->withTimestamps();
    }

    /**
     * Get the bottle types with stock information for this distribution center.
     * This maintains backward compatibility while working with the new product_category structure.
     */
    public function bottleTypeStocks(): BelongsToMany
    {
        // We need to join through ProductCategory since the direct relationship no longer exists
        return $this->belongsToMany(BottleType::class, 'product_category_distribution_center', 'distribution_center_id', 'product_category_id')
            ->join('product_categories', 'product_category_distribution_center.product_category_id', '=', 'product_categories.id')
            ->where('product_categories.product_type', 'bottle')
            ->where('product_categories.product_type_id', '=', DB::raw('bottle_types.id'))
            ->withPivot(['stock_empty', 'stock_filled'])
            ->withTimestamps();
    }

    /**
     * Get the total number of empty bottles in stock.
     */
    public function getTotalEmptyBottlesAttribute(): int
    {
        return $this->bottleTypeStocks()->sum('stock_empty');
    }

    /**
     * Get the total number of filled bottles in stock.
     */
    public function getTotalFilledBottlesAttribute(): int
    {
        return $this->bottleTypeStocks()->sum('stock_filled');
    }

    /**
     * Get the total number of bottles (empty + filled) in stock.
     */
    public function getTotalBottlesAttribute(): int
    {
        return $this->total_empty_bottles + $this->total_filled_bottles;
    }
}
