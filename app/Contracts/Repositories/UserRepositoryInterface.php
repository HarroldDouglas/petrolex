<?php

namespace App\Contracts\Repositories;

use App\Models\User;

interface UserRepositoryInterface
{
    /**
     * Find a user by email
     */
    public function findByEmail(string $email): ?User;

    /**
     * Find a user by phone number
     */
    public function findByPhone(string $phone): ?User;
}
