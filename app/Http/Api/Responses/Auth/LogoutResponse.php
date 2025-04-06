<?php

namespace App\Http\Api\Responses\Auth;

use App\Http\Api\Responses\ApiResponse;

class LogoutResponse extends ApiResponse
{
    public static function make(): self
    {
        // TODO: move this hard code text to translate file
        return new self(
            null,
            'Déconnexion réussie'
        );
    }
}
