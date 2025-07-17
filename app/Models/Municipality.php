<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Municipality extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'city_id',
    ];

    /**
     * Get the city that owns the municipality.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * The neighborhoods that belong to the municipality.
     */
    public function neighborhoods(): BelongsToMany
    {
        return $this->belongsToMany(Neighborhood::class, 'municipality_neighborhood');
    }
}