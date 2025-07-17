<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'country',
    ];

    /**
     * Get the neighborhoods for the city.
     */
    public function neighborhoods(): HasMany
    {
        return $this->hasMany(Neighborhood::class);
    }

    /**
     * Get the municipalities for the city.
     */
    public function municipalities(): HasMany
    {
        return $this->hasMany(Municipality::class);
    }
}