<?php

namespace App\Http\Resources\Customer;

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
            'neighborhood' => $this->whenLoaded('neighborhood', function () {
                return [
                    'id' => $this->neighborhood->id,
                    'name' => $this->neighborhood->name,
                    'municipality_id' => $this->neighborhood->municipality_id,
                ];
            }),
            'municipality' => $this->when($this->neighborhood && $this->neighborhood->municipality, function () {
                return [
                    'id' => $this->neighborhood->municipality->id,
                    'name' => $this->neighborhood->municipality->name,
                    'city_id' => $this->neighborhood->municipality->city_id,
                ];
            }),
            'city' => $this->when($this->city !== null, function () {
                return [
                    'id' => $this->city->id,
                    'name' => $this->city->name,
                    'country_id' => $this->city->country_id,
                ];
            }),
            'country' => $this->when($this->country !== null, function () {
                return [
                    'id' => $this->country->id,
                    'name' => $this->country->name,
                    'code' => $this->country->code,
                ];
            }),
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
