<?php

namespace App\Http\Api\Resources;

use App\Models\Geography\Neighborhood;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Neighborhood
 *
 * @property int $id
 * @property string $name
 * @property float|null $latitude
 * @property float|null $longitude
 * @property int $municipality_id
 * @property bool $is_active
 */
class NeighborhoodResource extends JsonResource
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
            'municipality_id' => $this->municipality_id,
            'municipality' => new MunicipalityResource($this->whenLoaded('municipality')),
            'is_active' => $this->is_active,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'city' => new CityResource($this->whenLoaded('city')),
        ];
    }
}
