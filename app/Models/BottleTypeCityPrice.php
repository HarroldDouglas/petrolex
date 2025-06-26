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
}
