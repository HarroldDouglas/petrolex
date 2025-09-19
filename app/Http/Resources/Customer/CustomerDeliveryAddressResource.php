<?php

namespace App\Http\Resources\Customer;

use App\Http\Api\Resources\CityResource;
use App\Http\Api\Resources\CountryResource;
use App\Http\Api\Resources\MunicipalityResource;
use App\Http\Api\Resources\NeighborhoodResource;
use App\Models\CustomerDeliveryAddress;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin CustomerDeliveryAddress */
class CustomerDeliveryAddressResource extends JsonResource
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
            'label' => $this->label,
            'address' => $this->address,
            'neighborhood' => NeighborhoodResource::make($this->whenLoaded('neighborhood')),
            'municipality' => $this->when($this->neighborhood?->relationLoaded('municipality'), function () {
                return MunicipalityResource::make($this->neighborhood->municipality);
            }),
            'city' => CityResource::make($this->whenLoaded('city')),
            'country' => CountryResource::make($this->whenLoaded('country')),
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'phone' => $this->phone,
            'phone_country_code' => $this->phone_country_code,
            'contact_firstname' => $this->contact_firstname,
            'contact_lastname' => $this->contact_lastname,
            'contact_full_name' => $this->contact_full_name,
            'email' => $this->email,
            'address_precision' => $this->address_precision,
            'is_default' => $this->is_default,
        ];
    }
}
