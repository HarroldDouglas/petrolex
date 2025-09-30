<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Role;

interface RoleRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get all roles with their relationships
     */
    public function getAllWithRelations(): Collection;

    /**
     * Find a role by name
     */
    public function findByName(string $name): ?Role;

    /**
     * Get roles by guard name
     */
    public function getByGuard(string $guardName = 'web'): Collection;

    /**
     * Check if a role has assigned users
     */
    public function hasUsers(Role $role): bool;
}