<?php

namespace App\Http\Api\Responses\Auth;

use App\Http\Api\Resources\UserResource;
use App\Http\Api\Responses\ApiResponse;
use App\Models\User;

class LoginResponse extends ApiResponse
{
    /**
     * Créer une réponse formatée pour une connexion réussie
     *
     * @param  User  $user  L'utilisateur qui vient de se connecter
     * @param  string  $token  Le jeton d'authentification généré
     */
    public static function fromUserAndToken(User $user, string $token): ApiResponse
    {
        return parent::success([
            'user' => new UserResource($user),
            'token' => $token,
        ], 'Authentification réussie - Connexion établie avec succès');
    }
}
