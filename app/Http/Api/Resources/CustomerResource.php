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
        /** @var Customer&\Illuminate\Database\Eloquent\Model $customer */
        $customer = $this->resource;

        // Get user data from the parent UserResource
        $userData = (new UserResource($customer->user))->toArray($request);

        // Merge user data with customer-specific data
        return array_merge(
            $userData,
            [
                'id' => $customer->id, // Override user ID with customer ID
                'deliveryAddresses' => CustomerDeliveryAddressResource::collection($customer->deliveryAddresses) ?? [],
                'current_balance' => $customer->current_balance ?? null,
            ]
        );
    }
}