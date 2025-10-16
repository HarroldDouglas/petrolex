<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface PermissionRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get all permissions with their relationships
     */
    public function getAllWithRelations(): Collection;

    /**
     * Find a permission by name
     */
    public function findByName(string $name): ?\Spatie\Permission\Models\Permission;

    /**
     * Get permissions by guard name
     */
    public function getByGuard(string $guardName = 'web'): Collection;
}