<?php

namespace App\Services\Role;

use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\Collection;

class RoleService
{
    /**
     * Get all roles
     */
    public function all(): Collection
    {
        return Role::with(['permissions', 'users'])->get();
    }

    /**
     * Find a role by ID
     */
    public function find(int $id): ?Role
    {
        return Role::with(['permissions', 'users'])->find($id);
    }

    /**
     * Find a role by ID or fail
     */
    public function findOrFail(int $id): Role
    {
        return Role::with(['permissions', 'users'])->findOrFail($id);
    }

    /**
     * Create a new role
     */
    public function create(array $data): Role
    {
        /** @var Role $role */
        $role = Role::create([
            'name' => $data['name'],
            'guard_name' => $data['guard_name'] ?? 'web',
        ]);

        if (isset($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return $role;
    }

    /**
     * Update an existing role
     */
    public function update(Role $role, array $data): Role
    {
        $role->update([
            'name' => $data['name'],
            'guard_name' => $data['guard_name'] ?? $role->guard_name,
        ]);

        if (isset($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return $role;
    }

    /**
     * Delete a role
     */
    public function delete(Role $role): bool
    {
        // Prevent deletion of system roles
        $systemRoles = ['super_admin', 'admin', 'manager', 'center_manager', 'delivery_person', 'customer', 'accountant', 'gas_manager'];
        if (in_array($role->name, $systemRoles)) {
            throw new \Exception('Cannot delete system role.');
        }

        // Check if role has users
        if ($role->users()->count() > 0) {
            throw new \Exception('Cannot delete role with assigned users.');
        }

        return $role->delete();
    }

    /**
     * Check if a role can be deleted
     */
    public function canDelete(Role $role): bool
    {
        $systemRoles = ['super_admin', 'admin', 'manager', 'center_manager', 'delivery_person', 'customer', 'accountant', 'gas_manager'];
        
        if (in_array($role->name, $systemRoles)) {
            return false;
        }

        return $role->users()->count() === 0;
    }
}