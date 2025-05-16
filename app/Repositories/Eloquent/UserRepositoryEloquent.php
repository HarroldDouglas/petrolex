<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;

class UserRepositoryEloquent implements UserRepositoryInterface
{
    /**
     * Find a user by email or phone
     */
    public function findByEmailOrPhone(string $identifier): ?User
    {
        return User::where('email', $identifier)
            ->orWhere('phone_number', $identifier)
            ->first();
    }

    /**
     * Update the user's remember token (used for OTP)
     */
    public function updateRememberToken(User $user, string $token): bool
    {
        return $user->update(['remember_token' => $token]);
    }

    /**
     * Find a user by email
     */
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Find a user by phone number
     */
    public function findByPhone(string $phone): ?User
    {
        return User::where('phone_number', $phone)->first();
    }
}
