<?php

namespace App\Http\Api\Responses\Auth;

use App\DTOs\Auth\TokenDTO;
use App\Enums\UserRole;
use App\Http\Api\Resources\CustomerResource;
use App\Http\Api\Responses\ApiResponse;
use App\Models\User;

class LoginResponse extends ApiResponse
{
    public static function withToken(TokenDTO $token): self
    {
        /** @var User $user */
        $user = $token->user;

        $data = [
            'access_token' => $token->accessToken,
            'token_type' => $token->tokenType,
        ];

        if ($user->hasRole(UserRole::CUSTOMER()->value)) {
            $data['user'] = new CustomerResource($user);
        }

        // TODO: move this hard code text to translate file
        return new self($data, __('Authentification réussie'));
    }
}
