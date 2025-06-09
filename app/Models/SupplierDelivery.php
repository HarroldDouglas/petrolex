<?php

namespace App\Models;

use App\Enums\SupplierDeliveryStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $distribution_center_id
 * @property int $user_id
 * @property float $total_amount
 * @property string|null $invoice_number
 * @property string|null $supplier_name
 * @property string|null $notes
 * @property Carbon $delivery_date
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 */
class SupplierDelivery extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'distribution_center_id',
        'user_id',
        'delivery_number',
        'supplier_name',
        'description',
        'delivery_date',
        'status',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'delivery_date' => 'date',
        'status' => SupplierDeliveryStatus::class,
    ];

    /**
     * Get the distribution center for this delivery.
     */
    public function distributionCenter(): BelongsTo
    {
        return $this->belongsTo(DistributionCenter::class);
    }

    /**
     * Get the user who created this delivery.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the product types included in this delivery.
     */
    public function productTypes(): HasMany
    {
        return $this->hasMany(SupplierDeliveryProductType::class);
    }

    /**
     * Get the bottles included in this delivery.
     */
    public function bottles(): HasMany
    {
        return $this->hasMany(SupplierDeliveryBottle::class);
    }

    /**
     * Get the bottle movements for this delivery.
     */
    public function bottleMovements(): HasMany
    {
        return $this->hasMany(BottleMovement::class);
    }
}
