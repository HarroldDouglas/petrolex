<?php

namespace App\Models;

use App\Enums\BottleStatus;
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
 * @property Product $product
 * @property BottleType $bottleType
 * @property DistributionCenter $distribution_center
 * @property-read int $movements_count
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property Carbon|null $marked_lost_at
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
        $productType = $this->product?->productCategory?->productType;

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

    public function scopeOfBottleType($query, int $bottleTypeId)
    {
        return $query->whereHas('product.productCategory', function ($query) use ($bottleTypeId) {
            $query->where('product_type_id', $bottleTypeId)
                ->where('product_type', 'bottle');
        });
    }
}
