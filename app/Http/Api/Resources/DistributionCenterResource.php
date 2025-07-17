<?php

namespace App\Http\Api\Resources;

use App\Models\DistributionCenter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin DistributionCenter
 */
class DistributionCenterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var DistributionCenter&\Illuminate\Database\Eloquent\Model $distributionCenter */
        $distributionCenter = $this->resource;

        return [
            'id' => $distributionCenter->id,
            'name' => $distributionCenter->name,
            'country' => $distributionCenter->country,
            'city' => $distributionCenter->city,
            'neighborhood' => $distributionCenter->neighborhood,
            'address' => $distributionCenter->address,
            'description' => $distributionCenter->description,
            'latitude' => $distributionCenter->latitude,
            'longitude' => $distributionCenter->longitude,
            'phone' => $distributionCenter->phone,
            'email' => $distributionCenter->email,
            'storage_capacity' => $distributionCenter->storage_capacity,
        ];
    }
}
