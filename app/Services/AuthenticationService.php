<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;

class AuthenticationService
{
    /**
     * Attempt to authenticate a user.
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function attemptLogin(array $credentials): User
    {
        if (! Auth::attempt($credentials)) {
            throw new AuthenticationException('Les identifiants fournis sont incorrects.');
        }

        return Auth::user();
    }

    /**
     * Generate a token for the user.
     */
    public function createToken(User $user, string $tokenName = 'api-token'): string
    {
        return $user->createToken($tokenName)->plainTextToken;
    }

    /**
     * Revoke the user's current access token.
     */
    public function revokeCurrentToken(User $user): void
    {
        $user->tokens()->where('id', $user->currentAccessToken()->id)->delete();
    }
}
