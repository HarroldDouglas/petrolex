<?php

namespace App\Http\Api\Resources;

use App\Models\DistributionCenter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin DistributionCenter
 *
 * @property int $id
 * @property string $name
 * @property string $country
 * @property string $city
 * @property string|null $neighborhood
 * @property string|null $address
 * @property string|null $description
 * @property float|null $latitude
 * @property float|null $longitude
 * @property string|null $phone
 * @property string|null $email
 * @property int|null $storage_capacity
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
        return [
            'id' => $this->id,
            'name' => $this->name,
            'country' => $this->country ? new CountryResource($this->country) : null,
            'city' => $this->city ? new CityResource($this->city) : null,
            'municipality' => $this->municipality ? new MunicipalityResource($this->municipality) : null,
            'neighborhood' => $this->neighborhood ? new NeighborhoodResource($this->neighborhood) : null,
            'address' => $this->address,
            'description' => $this->description,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'phone' => $this->phone,
            'email' => $this->email,
            'storage_capacity' => $this->storage_capacity,
        ];
    }
}
