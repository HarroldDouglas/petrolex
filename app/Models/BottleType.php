<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property float $content_price
 * @property float $bottle_with_content_price
 * @property float $bottle_only_price
 * @property float $deposit_price
 * @property int $weight_empty
 * @property int $weight_filled
 * @property bool $is_active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 */
class BottleType extends Model
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
        'capacity',
        'height',
        'width',
        'radius',
        'content_price',
        'bottle_with_content_price',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'height' => 'decimal:2',
        'width' => 'decimal:2',
        'radius' => 'decimal:2',
        'content_price' => 'decimal:2',
        'bottle_with_content_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the products that use this bottle type.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get the bottles of this type.
     */
    public function bottles(): HasMany
    {
        return $this->hasMany(Bottle::class);
    }

    /**
     * Get the supplier delivery product types for this bottle type.
     */
    public function supplierDeliveryProductTypes(): HasMany
    {
        return $this->hasMany(SupplierDeliveryProductType::class);
    }

    public function distributionCenterStocks(): BelongsToMany
    {
        return $this->belongsToMany(DistributionCenter::class)
            ->withPivot(['stock_empty', 'stock_filled'])
            ->withTimestamps();
    }
}
