<?php

namespace App\Models\Geography;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $municipality_id
 * @property-read Municipality $municipality
 */
class Neighborhood extends Model
{
    use HasFactory;

    protected $fillable = [
        'municipality_id',
        'name',
        'latitude',
        'longitude',
        'polygon',
        'is_active',
    ];

    protected $casts = [
        'polygon' => 'array',
    ];

    public function municipality(): BelongsTo
    {
        return $this->belongsTo(Municipality::class);
    }
}
