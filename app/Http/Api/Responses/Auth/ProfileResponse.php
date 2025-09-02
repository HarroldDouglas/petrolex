<?php

namespace App\Http\Api\Responses\Auth;

use App\Http\Api\Resources\UserResource;
use App\Http\Api\Responses\ApiResponse;
use App\Models\User;

class ProfileResponse extends ApiResponse
{
    public static function withUser(User $user): self
    {
        // Load the country relationship if not already loaded
        $user->loadMissing('country');

        // TODO: move this hard code text to translate file
        return new self(
            new UserResource($user),
            'Profil récupéré avec succès'
        );
    }
}
