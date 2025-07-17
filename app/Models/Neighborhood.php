<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Neighborhood extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'city_id',
    ];

    /**
     * Get the city that owns the neighborhood.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * The municipalities that belong to the neighborhood.
     */
    public function municipalities(): BelongsToMany
    {
        return $this->belongsToMany(Municipality::class, 'municipality_neighborhood');
    }
}