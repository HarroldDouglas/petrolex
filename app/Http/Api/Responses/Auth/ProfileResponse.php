<?php

namespace App\Http\Api\Responses\Auth;

use App\Enums\UserRole;
use App\Http\Api\Resources\CustomerResource;
use App\Http\Api\Resources\UserResource;
use App\Http\Api\Responses\ApiResponse;
use App\Models\User;

class ProfileResponse extends ApiResponse
{
    public static function withUser(User $user): self
    {
        $user->loadMissing([
            'country',
            'customer.deliveryAddresses.neighborhood.municipality.city.country',
        ]);

        $userResource = match (true) {
            $user->hasRole(UserRole::CUSTOMER()->value) => new CustomerResource($user->customer),
            default => new UserResource($user),
        };

        return new self(
            $userResource,
            'Profil récupéré avec succès'
        );
    }
}
