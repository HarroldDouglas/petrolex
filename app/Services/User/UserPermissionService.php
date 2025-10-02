<?php

namespace App\Services\User;

use App\Enums\UserPermissionState;
use App\Models\User;
use App\Services\Permission\PermissionService;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;

class UserPermissionService
{
    /**
     * Opacity constants for permission display
     */
    private const ROLE_PERMISSION_OPACITY = 0.6;
    private const DEFAULT_PERMISSION_OPACITY = 1.0;

    public function __construct(
        protected PermissionService $permissionService
    ) {}

    public function getUserPermissionStates(User $user): array
    {
        $allPermissions = $this->permissionService->getGroupedPermissions();

        return collect($allPermissions)
            ->map(fn ($moduleData, $module) => $this->buildModuleState($moduleData, $user))
            ->toArray();
    }

    /**
     * Build permission state for a specific module
     */
    private function buildModuleState(array $moduleData, User $user): array
    {
        return [
            'module' => $moduleData['module'],
            'permissions' => collect($moduleData['permissions'])
                ->map(fn ($permission) => $this->buildPermissionState($permission, $user))
                ->toArray(),
        ];
    }

    /**
     * Build permission state for a specific permission
     */
    private function buildPermissionState(array $permission, User $user): array
    {
        $permissionName = $permission['name'];
        $state = $user->getPermissionState($permissionName);
        $permissionState = UserPermissionState::tryFrom($state) ?? UserPermissionState::NONE;

        return [
            'name' => $permissionName,
            'label' => $permission['label'],
            'state' => $state,
            'checked' => $permissionState->isSelected(),
            'opacity' => $this->getPermissionOpacity($permissionState),
            'crossed' => $permissionState === UserPermissionState::REVOKED,
        ];
    }

    /**
     * Get opacity value for permission display
     */
    private function getPermissionOpacity(UserPermissionState $state): float
    {
        return $state === UserPermissionState::ROLE
            ? self::ROLE_PERMISSION_OPACITY
            : self::DEFAULT_PERMISSION_OPACITY;
    }

    public function toggleUserPermission(User $user, string $permissionName): void
    {
        $currentState = UserPermissionState::tryFrom($user->getPermissionState($permissionName)) ?? UserPermissionState::NONE;

        $this->executePermissionAction($user, $permissionName, $currentState);
    }

    /**
     * Execute the appropriate permission action based on current state
     */
    private function executePermissionAction(User $user, string $permissionName, UserPermissionState $currentState): void
    {
        match ($currentState) {
            UserPermissionState::ROLE => $user->revokeSpecificPermission($permissionName),
            UserPermissionState::DIRECT => $user->revokePermissionTo($permissionName),
            UserPermissionState::REVOKED => $user->restoreRevokedPermission($permissionName),
            UserPermissionState::NONE => $user->givePermissionTo($permissionName),
        };
    }

    public function getEffectiveUserPermissions(User $user): Collection
    {
        return $user->getEffectivePermissions()->pluck('name');
    }

    public function canManageUserPermissions(User $targetUser): bool
    {
        return $targetUser->canHavePermissionsManaged();
    }
}
