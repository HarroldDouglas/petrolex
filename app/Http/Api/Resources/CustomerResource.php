<?php

namespace App\Http\Api\Resources;

use App\Http\Resources\Customer\CustomerDeliveryAddressResource;
use App\Models\Customer;
use Illuminate\Http\Request;

/**
 * @mixin Customer
 *
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string $full_name
 * @property string $email
 * @property string $phone_number
 * @property string|null $address
 * @property string|null $email_verified_at
 * @property string|null $phone_verified_at
 * @property string|null $last_login_at
 * @property string[] $roles
 * @property string $created_at
 * @property int $customer_id
 * @property \App\Http\Resources\Customer\CustomerDeliveryAddressResource[] $deliveryAddresses
 * @property float|null $current_balance
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

        if ($customer->user) {
            $customer->user->loadMissing('country');
            $userData = (new UserResource($customer->user))->toArray($request);
        } else {
            $userData = [];
        }

        // Load delivery addresses with all geographic relations
        $customer->loadMissing([
            'deliveryAddresses.neighborhood.municipality.city.country',
        ]);

        return array_merge(
            $userData,
            [
                'customer_id' => $customer->id,
                'delivery_addresses' => CustomerDeliveryAddressResource::collection($customer->deliveryAddresses) ?? [],
                'current_balance' => $customer->current_balance ?? null,
            ]
        );
    }
}
