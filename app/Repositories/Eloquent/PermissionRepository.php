<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\PermissionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Permission;

class PermissionRepository extends BaseEloquentRepository implements PermissionRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new Permission());
    }

    /**
     * Get all permissions with their relationships
     */
    public function getAllWithRelations(): Collection
    {
        return Permission::with(['roles'])->get();
    }

    /**
     * Find a permission by name
     */
    public function findByName(string $name): ?Permission
    {
        return Permission::where('name', $name)->first();
    }

    /**
     * Get permissions by guard name
     */
    public function getByGuard(string $guardName = 'web'): Collection
    {
        return Permission::where('guard_name', $guardName)->get();
    }
}