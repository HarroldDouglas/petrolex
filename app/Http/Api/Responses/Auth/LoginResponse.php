<?php

namespace App\Http\Api\Responses\Auth;

use App\DTOs\Auth\TokenDTO;
use App\Http\Api\Responses\ApiResponse;

class LoginResponse extends ApiResponse
{
    public static function withToken(TokenDTO $token): self
    {
        // TODO: move this hard code text to translate file
        return new self([
            'access_token' => $token->accessToken,
            'token_type' => $token->tokenType,
        ], 'Authentification réussie');
    }
}
