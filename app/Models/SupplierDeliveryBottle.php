<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $supplier_delivery_id
 * @property int $supplier_delivery_product_type_id
 * @property int $bottle_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
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
        'supplier_delivery_id',
        'supplier_delivery_product_type_id',
        'bottle_id',
    ];

    /**
     * Get the supplier delivery this bottle belongs to.
     */
    public function supplierDelivery(): BelongsTo
    {
        return $this->belongsTo(SupplierDelivery::class);
    }

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
}
