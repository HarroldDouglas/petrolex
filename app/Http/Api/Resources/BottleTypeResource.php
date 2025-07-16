<?php

namespace App\Http\Api\Resources;

use App\Models\BottleType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin BottleType */
class BottleTypeResource extends JsonResource
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
            'description' => $this->description,
            'capacity' => $this->capacity,
            'height' => $this->height,
            'weight' => $this->weight,
            'radius' => $this->radius,
            'content_price' => $this->content_price,
            'bottle_with_content_price' => $this->bottle_with_content_price,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
