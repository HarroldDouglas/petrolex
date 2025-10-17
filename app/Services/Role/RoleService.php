<?php

namespace App\Services\Role;

use App\Events\Role\RoleDeletedEvent;
use App\Events\Role\RoleDeletingEvent;
use App\Events\Role\RolePermissionUpdatedEvent;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Services\BaseServiceForEntity;
use App\Services\Permission\PermissionService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class RoleService extends BaseServiceForEntity
{
    public function __construct(
        protected RoleRepositoryInterface $roleRepository,
        protected PermissionService $permissionService
    ) {
        parent::__construct($roleRepository);
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
     *
     * @param  Role  $role
     * @return Role
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
                /** @var Role $updatedRole */
                Log::warning('RoleService: No permissions data to assign', [
                    'role_id' => $updatedRole->id,
                    'data_keys' => array_keys($data),
                ]);
            }

            return $updatedRole;
        });
    }

    /**
     * Delete a role - permissions will be detached by event listener
     *
     * @param  Role  $role
     */
    public function delete(Model $role): bool
    {
        return $this->executeInTransaction(function () use ($role) {
            Log::info('RoleService: Starting role deletion', [
                'role_id' => $role->id,
                'role_name' => $role->name,
                'permissions_count' => $role->permissions()->count(),
                'users_count' => $role->users()->count(),
            ]);

            RoleDeletingEvent::dispatch($role);

            $deleted = $this->repository->delete($role);

            if ($deleted) {
                RoleDeletedEvent::dispatch($role);

                Log::info('RoleService: Role successfully deleted', [
                    'role_id' => $role->id,
                    'role_name' => $role->name,
                ]);
            } else {
                Log::error('RoleService: Failed to delete role', [
                    'role_id' => $role->id,
                    'role_name' => $role->name,
                ]);
            }

            return $deleted;
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
