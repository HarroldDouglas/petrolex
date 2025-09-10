<?php

namespace App\Http\Api\Resources;

use App\Models\Geography\Country;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Country
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property int $decimal_places
 */
class CountryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'decimal_places' => config('countries.default_decimal_places'),
        ];
    }
}
