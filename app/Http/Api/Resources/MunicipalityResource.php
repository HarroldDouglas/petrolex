<?php

namespace App\Http\Api\Resources;

use App\Models\Geography\Municipality;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Municipality
 *
 * @property int $id
 * @property string $name
 */
class MunicipalityResource extends JsonResource
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
            'city' => new CityResource($this->whenLoaded('city')),
            'neighborhoods' => NeighborhoodResource::collection($this->whenLoaded('neighborhoods')),
        ];
    }
}
