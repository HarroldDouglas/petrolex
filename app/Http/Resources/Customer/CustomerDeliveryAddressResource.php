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
        $neighborhood = null;
        $municipality = null;
        $city = null;
        $country = null;

        if ($this->relationLoaded('neighborhood') && $this->neighborhood) {
            $neighborhood = [
                'id' => $this->neighborhood->id,
                'name' => $this->neighborhood->name,
                'municipality_id' => $this->neighborhood->municipality_id,
                'is_active' => $this->neighborhood->is_active,
                'latitude' => $this->neighborhood->latitude,
                'longitude' => $this->neighborhood->longitude,
            ];

            if ($this->neighborhood->relationLoaded('municipality') && $this->neighborhood->municipality) {
                $municipality = [
                    'id' => $this->neighborhood->municipality->id,
                    'name' => $this->neighborhood->municipality->name,
                    'city_id' => $this->neighborhood->municipality->city_id,
                ];

                if ($this->neighborhood->municipality->relationLoaded('city') && $this->neighborhood->municipality->city) {
                    $city = [
                        'id' => $this->neighborhood->municipality->city->id,
                        'name' => $this->neighborhood->municipality->city->name,
                        'country_id' => $this->neighborhood->municipality->city->country_id,
                    ];

                    if ($this->neighborhood->municipality->city->relationLoaded('country') && $this->neighborhood->municipality->city->country) {
                        $countryObj = $this->neighborhood->municipality->city->country;
                        $country = [
                            'id' => $countryObj->id,
                            'name' => $countryObj->name,
                            'code' => $countryObj->code,
                            'phone_code' => $countryObj->phone_code,
                            'currency' => $countryObj->currency?->label,
                            'decimal_places' => $countryObj->currency?->decimalPlaces(),
                            'is_active' => $countryObj->is_active,
                        ];
                    }
                }
            }
        }

        return [
            'id' => $this->id,
            'label' => $this->label,
            'address' => $this->address,
            'neighborhood' => $neighborhood,
            'municipality' => $municipality,
            'city' => $city,
            'country' => $country,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'location_link' => $this->location_link,
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
