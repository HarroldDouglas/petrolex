<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\RoleRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Role;

class RoleRepository extends BaseEloquentRepository implements RoleRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new Role());
    }

    /**
     * Get all roles with their relationships
     */
    public function getAllWithRelations(): Collection
    {
        return Role::with(['permissions', 'users'])->get();
    }

    /**
     * Find a role by name
     */
    public function findByName(string $name): ?Role
    {
        return Role::where('name', $name)->first();
    }

    /**
     * Get roles by guard name
     */
    public function getByGuard(string $guardName = 'web'): Collection
    {
        return Role::where('guard_name', $guardName)
            ->with(['permissions', 'users'])
            ->get();
    }

    /**
     * Check if a role has assigned users
     */
    public function hasUsers(Role $role): bool
    {
        return $role->users()->exists();
    }
}
