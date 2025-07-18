<?php

namespace App\Models;

use App\Models\Geography\City;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $product_category_id
 * @property int $city_id // ✅ Changé de 'city' à 'city_id'
 * @property float $content_price
 * @property float $content_with_bottle_price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * // Relations
 * @property-read ProductCategory $productCategory
 * @property-read City $city  // ✅ Nouvelle relation
 */
class ProductCategoryCityPrice extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'product_category_city_prices';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'product_category_id',
        'city_id',
        'content_price',
        'content_with_bottle_price',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'content_price' => 'decimal:2',
        'content_with_bottle_price' => 'decimal:2',
    ];

    /**
     * Get the product category that owns this price record.
     */
    public function productCategory(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class);
    }

    /**
     * ✅ Nouvelle relation avec City
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }
}
