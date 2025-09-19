<?php

namespace App\Http\Api\Responses\Auth;

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

        return new self(
            new UserResource($user),
            'Profil récupéré avec succès'
        );
    }
}
