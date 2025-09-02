<?php

namespace App\Repositories\Contracts;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface extends BaseRepositoryInterface
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

    /**
     * Find a user by phone number and country
     */
    public function findByPhoneAndCountry(string $phone, int $countryId): ?User;

    /**
     * Finds and returns a collection of users based on their assigned role.
     *
     * @param  UserRole  $userRole  The role to search for (e.g., UserRole::ADMIN, UserRole::EDITOR).
     * @return Collection<User> Returns a collection of User models that match the specified role.
     */
    public function findByRole(UserRole $userRole): Collection;
}
