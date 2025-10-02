<?php

namespace App\Services\Permission;

use App\Enums\PermissionEnum;
use App\Repositories\Contracts\PermissionRepositoryInterface;
use App\Services\BaseServiceForEntity;
use Spatie\Permission\Models\Permission;

class PermissionService extends BaseServiceForEntity
{
    public function __construct(
        protected PermissionRepositoryInterface $permissionRepository
    ) {
        parent::__construct($this->permissionRepository);
    }

    protected function getModel(): string
    {
        return Permission::class;
    }

        /**
     * Get all permissions grouped by module
     * 
     * @return array<string, array{module: string, permissions: array<int, array{name: string, label: string}>}>
     */
    public function getGroupedPermissions(): array
    {
        $permissions = $this->permissionRepository->getAllWithRelations();

        return $permissions
            ->groupBy(function (Permission $permission) {
                return $this->getModuleFromPermission($permission->name);
            })
            ->map(function ($permissions, $module) {
                return [
                    'module' => $module,
                    'permissions' => $permissions->map(function (Permission $permission) {
                        return [
                            'name' => $permission->name,
                            'label' => $this->formatPermissionLabel($permission->name),
                        ];
                    })->toArray(),
                ];
            })
            ->toArray();
    }

    /**
     * Extract and format module name from permission using PermissionEnum
     */
    protected function getModuleFromPermission(string $permissionName): string
    {
        $parts = explode('.', $permissionName);
        $module = $parts[0];

        $moduleLabels = PermissionEnum::moduleLabels();

        return $moduleLabels[$module] ?? ucfirst(str_replace('_', ' ', $module));
    }

    /**
     * Format permission label using PermissionEnum
     */
    protected function formatPermissionLabel(string $permissionName): string
    {
        $labels = PermissionEnum::labels();

        return $labels[$permissionName] ?? ucfirst(str_replace(['.', '_'], ' ', $permissionName));
    }
}
