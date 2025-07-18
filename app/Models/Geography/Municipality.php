<?php

namespace App\Models\Geography;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $city_id
 * @property-read \App\Models\Geography\City $city
 */
class Municipality extends Model
{
    use HasFactory;

    protected $fillable = [
        'city_id',
        'name',
        'is_active',
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function neighborhoods(): HasMany
    {
        return $this->hasMany(Neighborhood::class);
    }
}
