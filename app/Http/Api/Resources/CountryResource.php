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
 * @property string $phone_code
 * @property string $currency
 * @property bool $is_active
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
            'phone_code' => $this->phone_code,
            'currency' => $this->currency,
            'decimal_places' => config('countries.default_decimal_places'),
            'is_active' => $this->is_active,
        ];
    }
}
