<?php

namespace App\Services\Role;

use App\Events\Role\RolePermissionsUpdatedEvent;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Services\BaseServiceForEntity;
use App\Services\Permission\PermissionService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class RoleService extends BaseServiceForEntity
{
    public function __construct(
        protected RoleRepositoryInterface $roleRepository,
        protected PermissionService $permissionService
    ) {
        parent::__construct($this->roleRepository);
    }

    protected function getModel(): string
    {
        return Role::class;
    }

    /**
     * Create a new role with permissions
     */
    public function create(array $data): Model
    {
        return $this->executeInTransaction(function () use ($data) {
            // Create the role
            $role = $this->repository->create([
                'name' => $data['name'],
                'guard_name' => $data['guard_name'] ?? 'web',
            ]);

            // Dispatch event to assign permissions
            if (isset($data['permissions']) && is_array($data['permissions'])) {
                event(new RolePermissionsUpdatedEvent($role, $data['permissions']));
            }

            return $role;
        });
    }

    /**
     * Update an existing role with permissions
     */
    public function update(Model $role, array $data): Model
    {
        return $this->executeInTransaction(function () use ($role, $data) {
            // Update the role
            $updatedRole = $this->repository->update($role, [
                'name' => $data['name'],
                'guard_name' => $data['guard_name'] ?? $role->guard_name,
            ]);

            // Dispatch event to assign permissions
            if (isset($data['permissions']) && is_array($data['permissions'])) {
                event(new RolePermissionsUpdatedEvent($updatedRole, $data['permissions']));
            }

            return $updatedRole;
        });
    }

    /**
     * Get available permissions grouped by modules
     */
    public function getAvailablePermissions(): array
    {
        return $this->permissionService->getGroupedPermissions();
    }
}