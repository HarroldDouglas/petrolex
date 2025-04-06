<?php

namespace App\Http\Api\Resources;

use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
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
            'is_active' => $user->is_active,
            'email_verified_at' => $user->email_verified_at instanceof CarbonInterface ? $user->email_verified_at->toISOString() : null,
            'phone_verified_at' => $user->phone_verified_at instanceof CarbonInterface ? $user->phone_verified_at->toISOString() : null,
            'last_login_at' => $user->last_login_at instanceof CarbonInterface ? $user->last_login_at->toISOString() : null,
            'roles' => $user->getRoleNames(),
            'created_at' => $user->created_at->toISOString(),
            'updated_at' => $user->updated_at->toISOString(),
        ];
    }
}
