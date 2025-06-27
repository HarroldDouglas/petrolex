<?php

namespace App\Models;

use App\Enums\BottleStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $product_id
 * @property int $distribution_center_id
 * @property int|null $marked_lost_by_user_id
 * @property string $barcode
 * @property bool $is_filled
 * @property BottleStatus $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property Carbon|null $marked_lost_at
 *
 * // Relations
 * @property-read Product $product
 * @property-read DistributionCenter $distributionCenter
 * @property-read \Illuminate\Database\Eloquent\Collection<int, BottleMovement> $movements
 * @property-read \Illuminate\Database\Eloquent\Collection<int, SupplierDeliveryBottle> $supplierDeliveryBottles
 *
 * // Accessors
 * @property-read BottleType|null $bottleType
 * @property-read int|null $bottleTypeId
 *
 * // Query Scopes
 *
 * @method static Builder ofBottleType(int $bottleTypeId)
 */
class Bottle extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'product_id',
        'distribution_center_id',
        'barcode',
        'is_filled',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_filled' => 'boolean',
        'status' => BottleStatus::class,
    ];

    /**
     * Get the product associated with the bottle.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the bottle type of the bottle.
     */
    public function getBottleTypeAttribute(): ?BottleType
    {
        $productType = $this->product?->productCategory?->productTypeInstance;

        if ($productType instanceof BottleType) {
            return $productType;
        }

        return null;
    }

    public function getBottleTypeIdAttribute(): ?int
    {
        return $this->bottleType?->id;
    }

    /**
     * Get the distribution center where the bottle is stored.
     */
    public function distributionCenter(): BelongsTo
    {
        return $this->belongsTo(DistributionCenter::class);
    }

    /**
     * Get the movements of this bottle.
     */
    public function movements(): HasMany
    {
        return $this->hasMany(BottleMovement::class);
    }

    /**
     * Get the supplier delivery bottles for this bottle.
     */
    public function supplierDeliveryBottles(): HasMany
    {
        return $this->hasMany(SupplierDeliveryBottle::class);
    }

    public function scopeOfBottleType(Builder $query, int $bottleTypeId): Builder
    {
        return $query->whereHas('product.productCategory', function (Builder $query) use ($bottleTypeId): void {
            $query->where('product_type_id', $bottleTypeId)
                ->where('product_type', 'bottle');
        });
    }
}
