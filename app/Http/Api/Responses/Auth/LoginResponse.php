<?php

namespace App\Http\Api\Responses\Auth;

use App\DTOs\Auth\AuthDTO;
use App\Enums\UserRole;
use App\Http\Api\Resources\CustomerResource;
use App\Http\Api\Resources\UserResource;
use App\Http\Api\Responses\ApiResponse;
use App\Models\User;

class LoginResponse extends ApiResponse
{
    public static function withToken(AuthDTO $authDTO): self
    {
        /** @var User $user */
        $user = $authDTO->user;

        $userResource = match (true) {
            $user->hasRole(UserRole::CUSTOMER()->value) => new CustomerResource($user),
            default => new UserResource($user),
        };

        $data = [
            'access_token' => $authDTO->token->accessToken,
            'token_type' => $authDTO->token->tokenType,
            'user' => $userResource,
        ];

        // TODO: move this hard coded text to translation files
        return new self($data, __('Authentification réussie'));
    }
}
