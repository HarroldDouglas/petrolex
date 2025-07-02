<?php

namespace App\Models;

use App\Enums\SupplierDeliveryStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
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
 * @property Carbon $supply_date
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 *
 * // Relations
 * @property-read DistributionCenter $distributionCenter
 * @property-read User $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, SupplierDeliveryProductType> $productTypes
 * @property-read \Illuminate\Database\Eloquent\Collection<int, SupplierDeliveryBottle> $bottles
 * @property-read \Illuminate\Database\Eloquent\Collection<int, BottleMovement> $bottleMovements
 *
 * // Accessors
 *
 * // Query Scopes
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
        'title',
        'supplier_name',
        'description',
        'supply_date',
        'status',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'supply_date' => 'datetime',
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
    public function bottles(): HasManyThrough
    {
        return $this->hasManyThrough(
            SupplierDeliveryBottle::class,
            SupplierDeliveryProductType::class
        );
    }

    /**
     * Get the bottle movements for this delivery.
     */
    public function bottleMovements(): HasMany
    {
        return $this->hasMany(BottleMovement::class);
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $supplierDelivery): void {
            $supplierDelivery->setDefaultValues();
        });
    }

    /**
     * Set default values for empty fields
     */
    private function setDefaultValues(): void
    {
        if (empty($this->delivery_number)) {
            $this->delivery_number = self::generateDeliveryNumber();
        }

        if (empty($this->supplier_name)) {
            $this->supplier_name = 'PETROLEX';
        }
    }

    /**
     * Generate a unique delivery number
     */
    public static function generateDeliveryNumber(): string
    {
        $prefix = 'SUP';
        $year = now()->format('Y');
        $month = now()->format('m');

        $lastDelivery = self::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastDelivery && preg_match('/(\d+)$/', $lastDelivery->delivery_number, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('%s-%s%s-%04d', $prefix, $year, $month, $nextNumber);
    }

    /**
     * Check if the supply can be edited.
     */
    public function canBeEdited(): bool
    {
        return $this->status->value === SupplierDeliveryStatus::IN_PROGRESS();
    }
}
