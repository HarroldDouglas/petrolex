<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BottleTypeCityPrice extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'bottle_type_city_prices';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'bottle_type_id',
        'city',
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
     * Get the bottle type that owns this price record.
     */
    public function bottleType(): BelongsTo
    {
        return $this->belongsTo(BottleType::class);
    }

    /**
     * Get the price difference between content with bottle and content only.
     */
    public function getBottlePriceAttribute(): float
    {
        return $this->content_with_bottle_price - $this->content_price;
    }
}
