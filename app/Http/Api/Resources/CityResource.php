<?php

namespace App\Http\Api\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use App\Models\Geography\City;

/**
 * @mixin City
 *
 * @property int $id
 * @property string $name
 */
class CityResource extends JsonResource
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
            'country' => new CountryResource($this->whenLoaded('country')),
        ];
    }
}
