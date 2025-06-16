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
 * @property int $bottle_type_id
 * @property int $distribution_center_id
 * @property int|null $marked_lost_by_user_id
 * @property string $barcode
 * @property bool $is_filled
 * @property BottleStatus $status
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
        'bottle_type_id',
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
    public function bottleType(): BelongsTo
    {
        return $this->belongsTo(BottleType::class);
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
}
