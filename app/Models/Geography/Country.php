<?php

namespace App\Models\Geography;

use App\Enums\Currency;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'phone_code',
        'currency',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }

    /**
     * Handle nullable currency attribute
     */
    protected function currency(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Currency::from($value) : null,
            set: fn ($value) => $value instanceof Currency ? $value->value : $value,
        );
    }
}
