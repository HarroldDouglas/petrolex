<?php

namespace App\Http\Api\Resources;

use App\Http\Resources\Customer\CustomerDeliveryAddressResource;
use App\Models\Customer;
use Illuminate\Http\Request;

/**
 * @mixin Customer
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
        /** @var Customer $customer */
        $customer = $this->resource;

        return array_merge(
            UserResource::make($customer->user)->toArray($request),
            [
                'deliveryAddresses' => CustomerDeliveryAddressResource::collection($customer->deliveryAddresses) ?? [],
                'current_balance' => $customer->current_balance ?? null,
            ]
        );

    }
}
