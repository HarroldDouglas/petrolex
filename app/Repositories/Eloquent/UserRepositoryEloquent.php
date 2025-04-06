<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;

class UserRepositoryEloquent implements UserRepositoryInterface
{
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
