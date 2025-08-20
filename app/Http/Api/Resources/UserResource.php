<?php

namespace App\Http\Api\Resources;

use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
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
 */
class UserResource extends JsonResource
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

        return [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'phone_number' => $user->phone_number,
            'address' => $user->address,
            'email_verified_at' => $user->email_verified_at instanceof CarbonInterface ? $user->email_verified_at->toISOString() : null,
            'phone_verified_at' => $user->phone_verified_at instanceof CarbonInterface ? $user->phone_verified_at->toISOString() : null,
            'last_login_at' => $user->last_login_at instanceof CarbonInterface ? $user->last_login_at->toISOString() : null,
            'roles' => $user->getRoleNames(),
            'customer_id' => $this->when($user->isCustomer(), $user->customer->id ?? null),
            'delivery_person_id' => $this->when($user->isDeliveryPerson(), $user->deliveryPerson->id ?? null),
            'created_at' => $user->created_at->toISOString(),
        ];
    }
}
