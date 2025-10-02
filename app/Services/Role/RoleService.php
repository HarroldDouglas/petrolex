<?php

namespace App\Services\Role;

use App\Events\Role\RolePermissionUpdatedEvent;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Services\BaseServiceForEntity;
use App\Services\Permission\PermissionService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
            
            $role = $this->repository->create([
                'name' => $data['name'],
                'guard_name' => $data['guard_name'] ?? 'web',
            ]);

            if (isset($data['permissions']) && is_array($data['permissions'])) {
                event(new RolePermissionUpdatedEvent($role, $data['permissions']));
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
           
            $updatedRole = $this->repository->update($role, [
                'name' => $data['name'],
                'guard_name' => $data['guard_name'] ?? $role->guard_name,
            ]);

            if (isset($data['permissions']) && is_array($data['permissions'])) {
                event(new RolePermissionUpdatedEvent($updatedRole, $data['permissions']));
            } else {
                Log::warning("RoleService: No permissions data to assign", [
                    'role_id' => $updatedRole->id,
                    'data_keys' => array_keys($data)
                ]);
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