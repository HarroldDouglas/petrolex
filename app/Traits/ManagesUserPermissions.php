<?php

namespace App\Traits;

use App\Enums\UserPermissionState;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Permission;

trait ManagesUserPermissions
{
    /**
     * Get permissions that have been explicitly revoked for this user
     */
    public function revokedPermissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'user_revoked_permissions',
            'user_id',
            'permission_id'
        )->withTimestamps();
    }

    /**
     * Revoke a specific permission for this user (override role permission)
     */
    public function revokeSpecificPermission(string|Permission $permission): self
    {
        $permissionModel = $this->resolvePermissionModel($permission);

        if ($permissionModel && ! $this->hasRevokedPermission($permissionModel)) {
            $this->revokedPermissions()->attach($permissionModel->id);
        }

        return $this;
    }

    /**
     * Restore a revoked permission for this user
     */
    public function restoreRevokedPermission(string|Permission $permission): self
    {
        $permissionModel = $this->resolvePermissionModel($permission);

        if ($permissionModel) {
            $this->revokedPermissions()->detach($permissionModel->id);
        }

        return $this;
    }

    /**
     * Resolve permission to model instance
     */
    private function resolvePermissionModel(string|Permission $permission): ?Permission
    {
        return is_string($permission)
            ? Permission::where('name', $permission)->first()
            : $permission;
    }

    /**
     * Check if a specific permission is revoked for this user
     */
    public function hasRevokedPermission(string|Permission $permission): bool
    {
        $permissionModel = $this->resolvePermissionModel($permission);

        if (! $permissionModel) {
            return false;
        }

        return $this->revokedPermissions()->where('permission_id', $permissionModel->id)->exists();
    }

    /**
     * Get all effective permissions for this user (role + direct - revoked)
     */
    public function getEffectivePermissions(): \Illuminate\Support\Collection
    {
        $rolePermissions = $this->getAllPermissions();
        $revokedPermissionNames = $this->revokedPermissions()->pluck('name');

        return $rolePermissions->reject(function ($permission) use ($revokedPermissionNames) {
            return $revokedPermissionNames->contains($permission->name);
        });
    }

    /**
     * Check if user has effective permission (considering revoked permissions)
     */
    public function hasEffectivePermission(string|Permission $permission): bool
    {
        $permissionName = is_string($permission) ? $permission : $permission->name;

        if ($this->hasRevokedPermission($permissionName)) {
            return false;
        }

        return $this->hasPermissionTo($permissionName);
    }

    /**
     * Get permissions by source type for this user
     */
    public function getPermissionsBySource(): array
    {
        return [
            'role_permissions' => $this->getRolePermissionNames(),
            'direct_permissions' => $this->getDirectPermissionNames(),
            'revoked_permissions' => $this->getRevokedPermissionNames(),
        ];
    }

    /**
     * Get permission names from user roles
     */
    private function getRolePermissionNames(): \Illuminate\Support\Collection
    {
        if ($this->roles->count() === 0) {
            return collect();
        }

        return $this->roles()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->pluck('name')
            ->unique();
    }

    /**
     * Get direct permission names
     */
    private function getDirectPermissionNames(): \Illuminate\Support\Collection
    {
        return $this->permissions()->pluck('name');
    }

    /**
     * Get revoked permission names
     */
    private function getRevokedPermissionNames(): \Illuminate\Support\Collection
    {
        return $this->revokedPermissions()->pluck('name');
    }

    /**
     * Determine permission state for UI display
     */
    public function getPermissionState(string $permissionName): string
    {
        $permissions = $this->getPermissionsBySource();

        return $this->determinePermissionState($permissionName, $permissions);
    }

    /**
     * Determine the permission state based on source collections
     */
    private function determinePermissionState(string $permissionName, array $permissions): string
    {
        if ($permissions['revoked_permissions']->contains($permissionName)) {
            return UserPermissionState::REVOKED()->value;
        }

        if ($permissions['direct_permissions']->contains($permissionName)) {
            return UserPermissionState::DIRECT()->value;
        }

        if ($permissions['role_permissions']->contains($permissionName)) {
            return UserPermissionState::ROLE()->value;
        }

        return UserPermissionState::NONE()->value;
    }

    /**
     * Check if user can manage permissions (not customer or delivery person)
     */
    public function canHavePermissionsManaged(): bool
    {
        return ! $this->isCustomer() && ! $this->isDeliveryPerson();
    }
}
