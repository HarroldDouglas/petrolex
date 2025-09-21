<?php

declare(strict_types=1);

namespace App\Http\Api\Resources;

use App\Models\DeliveryPerson;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin DeliveryPerson
 */
class DeliveryPersonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var DeliveryPerson $deliveryPerson */
        $deliveryPerson = $this->resource;

        if (! $deliveryPerson) {
            return [];
        }

        return [
            'id' => $deliveryPerson->id,
            'user_id' => $deliveryPerson->user->id,
            'first_name' => $deliveryPerson->user->first_name,
            'last_name' => $deliveryPerson->user->last_name,
            'full_name' => $deliveryPerson->user->full_name,
            'phone_number' => $deliveryPerson->user->phone_number,
            'email' => $deliveryPerson->user->email,
        ];
    }
}
