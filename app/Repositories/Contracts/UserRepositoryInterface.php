<?php

namespace App\Repositories\Contracts;

use App\Models\User;

interface UserRepositoryInterface extends baseRepositoryInterface
{
    /**
     * Find a user by email or phone
     */
    public function findByEmailOrPhone(string $identifier): ?User;

    /**
     * Update the user's remember token (used for OTP)
     */
    public function updateRememberToken(User $user, string $token): bool;

    /**
     * Find a user by email
     */
    public function findByEmail(string $email): ?User;

    /**
     * Find a user by phone number
     */
    public function findByPhone(string $phone): ?User;
}
