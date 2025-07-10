<?php

namespace App\Http\Api\Resources;

use App\Models\User;
use Illuminate\Http\Request;

/**
 * @mixin User
 */
class CustomerResource extends UserResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var User&\Illuminate\Database\Eloquent\Model $user */
        $user = $this->resource;

        return array_merge(
            parent::toArray($request),
            [
                'deliveryAddresses' => $user->customer?->deliveryAddresses
                    ->map(fn ($address) => [
                        'id' => $address->id,
                        'label' => $address->label,
                        'address' => $address->address,
                        "neighborhood" => $address->neighborhood,
                        "country" => $address->country,
                        "latitude" => $address->latitude,
                        "longitude" => $address->longitude,
                        "phone" => $address->phone,
                        "contact_name" => $address->contact_name,
                        'city' => $address->city,
                        'postal_code' => $address->postal_code,
                    ]) ?? [],
                'current_balance' => $user->customer?->current_balance ?? null,
            ]
        );

    }
}
