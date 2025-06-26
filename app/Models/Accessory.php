<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $product_id
 * @property int $distribution_center_id
 * @property string|null $sku
 * @property bool $is_sold
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property Product $product
 * @property AccessoryType $accessoryType
 */
class Accessory extends Model
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
        'sku',
        'is_sold',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_sold' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::creating(function ($accessory) {
            if (empty($accessory->sku)) {
                $accessory->sku = self::generateSku();
            }
        });
    }

    /**
     * Generate a unique SKU for an accessory in the format date-time-random
     * Example: 12062025-060159-kpmzike
     */
    public static function generateSku(): string
    {
        $date = now()->format('dmY');
        $time = now()->format('His');
        $random = Str::lower(Str::random(7));

        return "{$date}-{$time}-{$random}";
    }

    /**
     * Get the product associated with the accessory.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the accessory type of the accessory.
     */
    public function getAccessoryTypeAttribute(): ?AccessoryType
    {
        $productType = $this->product?->productCategory?->productType;

        if ($productType instanceof AccessoryType) {
            return $productType;
        }

        return null;
    }

    /**
     * Get the accessory type ID of the accessory.
     */
    public function getAccessoryTypeIdAttribute(): ?int
    {
        return $this->accessoryType?->id;
    }

    /**
     * Get the distribution center where the accessory is stored.
     */
    public function distributionCenter(): BelongsTo
    {
        return $this->belongsTo(DistributionCenter::class);
    }

    /**
     * Scope a query to only include accessories of a specific accessory type.
     */
    public function scopeOfAccessoryType($query, int $accessoryTypeId)
    {
        return $query->whereHas('product.productCategory', function ($query) use ($accessoryTypeId) {
            $query->where('product_type_id', $accessoryTypeId)
                ->where('product_type', 'accessory');
        });
    }
}
