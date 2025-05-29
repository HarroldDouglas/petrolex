<?php

namespace App\Services\Permissions;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Collection;

class PermissionService
{
    /**
     * Get available roles that a user can assign based on their own role
     */
    public function getAvailableRolesForUser(User $user): Collection
    {
        if ($user->hasRole(UserRole::SUPER_ADMIN()->value)) {
            return collect(UserRole::cases());
        }

        if ($user->hasRole(UserRole::ADMIN()->value)) {
            return collect(UserRole::cases())->filter(function ($role) {
                return $role->value !== UserRole::SUPER_ADMIN()->value;
            });
        }

        if ($user->hasRole(UserRole::MANAGER()->value)) {
            return collect([
                UserRole::CENTER_MANAGER(),
                UserRole::DELIVERY_PERSON(),
                UserRole::CUSTOMER(),
            ]);
        }

        if ($user->hasRole(UserRole::CENTER_MANAGER()->value)) {
            return collect([UserRole::DELIVERY_PERSON(), UserRole::CUSTOMER()]);
        }

        return collect();
    }

    /**
     * Check if a user can assign a specific role
     */
    public function canAssignRole(User $user, string $roleName): bool
    {
        return $this->getAvailableRolesForUser($user)
            ->contains(fn ($role) => $role->value === $roleName);
    }
}
