<?php

namespace App\Models;

use App\Enums\SupplierDeliveryBottleMovementType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $supplier_delivery_product_type_id
 * @property int $bottle_id
 * @property string $movement_type
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property-read SupplierDeliveryProductType $bottleType
 * @property-read Bottle $bottle
 *
 * @method static Builder|static incoming()
 * @method static Builder|static outgoing()
 */
class SupplierDeliveryBottle extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'supplier_delivery_product_type_id',
        'bottle_id',
        'movement_type',
    ];

    /**
     * Get the supplier delivery bottle type this bottle belongs to.
     */
    public function bottleType(): BelongsTo
    {
        return $this->belongsTo(SupplierDeliveryProductType::class, 'supplier_delivery_product_type_id');
    }

    /**
     * Get the bottle record this belongs to.
     */
    public function bottle(): BelongsTo
    {
        return $this->belongsTo(Bottle::class);
    }

    /**
     * Scope a query to only include incoming bottles.
     */
    public function scopeIncoming(Builder $query): Builder
    {
        return $query->where('movement_type', SupplierDeliveryBottleMovementType::INCOMING()->value);
    }

    /**
     * Scope a query to only include outgoing bottles.
     */
    public function scopeOutgoing(Builder $query): Builder
    {
        return $query->where('movement_type', SupplierDeliveryBottleMovementType::OUTGOING()->value);
    }
}
