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
                'current_balance' => $user->customer?->current_balance ?? null,
            ]
        );

    }
}
