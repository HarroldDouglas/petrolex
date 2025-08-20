<?php

namespace App\Repositories\Eloquent;

use App\Enums\UserRole;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class UserRepository extends BaseEloquentRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

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
        return User::where('email', $email)->with(['customer', 'deliveryPerson'])->first();
    }

    /**
     * Find a user by phone number
     */
    public function findByPhone(string $phone): ?User
    {
        return User::where('phone_number', $phone)->with(['customer', 'deliveryPerson'])->first();
    }

    /**
     * Finds and returns a collection of users based on their assigned role.
     *
     * @param  UserRole  $userRole  The role to search for (e.g., UserRole::ADMIN, UserRole::EDITOR).
     * @return Collection<User> Returns a collection of User models that match the specified role.
     */
    public function findByRole(UserRole $userRole): Collection
    {
        return User::role($userRole)->get();
    }
}
