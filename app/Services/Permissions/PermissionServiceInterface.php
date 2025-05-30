<?php

namespace App\Services\Permissions;

use App\Models\User;
use Illuminate\Support\Collection;

interface PermissionServiceInterface
{
    /**
     * Get available roles that a user can assign based on their own role
     */
    public function getAvailableRolesForUser(User $user): Collection;

    /**
     * Check if a user can assign a specific role
     */
    public function canAssignRole(User $user, string $roleName): bool;
}
