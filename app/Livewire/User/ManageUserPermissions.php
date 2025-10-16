<?php

namespace App\Livewire\User;

use App\Enums\UserPermissionState;
use App\Models\User;
use App\Services\User\UserPermissionService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class ManageUserPermissions extends Component
{
    public User $user;
    public array $permissionStates = [];
    public array $selectedPermissions = [];
    protected UserPermissionService $userPermissionService;

    public function boot(UserPermissionService $userPermissionService)
    {
        $this->userPermissionService = $userPermissionService;
    }

    public function mount(User $user)
    {
        if (! $this->userPermissionService->canManageUserPermissions($user)) {
            abort(403, 'Les permissions de cet utilisateur ne peuvent pas être gérées.');
        }

        $this->user = $user;
        $this->loadPermissionStates();
        $this->initializeSelectedPermissions();
    }

    public function loadPermissionStates()
    {
        $this->permissionStates = $this->userPermissionService->getUserPermissionStates($this->user);
    }
    public function initializeSelectedPermissions()
    {
        $this->selectedPermissions = $this->getAllPermissions()
            ->filter(fn ($permission) => $permission['checked'])
            ->pluck('name')
            ->toArray();
    }

    /**
     * Get the current permission state for a given permission
     */
    public function getCurrentPermissionState(string $permissionName): string
    {
        $originalPermission = $this->findOriginalPermission($permissionName);

        if (! $originalPermission) {
            return UserPermissionState::NONE()->value;
        }

        $isCurrentlySelected = $this->isPermissionSelected($permissionName);
        $originalState = UserPermissionState::tryFrom($originalPermission['state']) ?? UserPermissionState::NONE();

        return $this->getCurrentState($originalState, $isCurrentlySelected)->value;
    }

    /**
     * Find the original permission data by name
     */
    private function findOriginalPermission(string $permissionName): ?array
    {
        return collect($this->permissionStates)
            ->flatMap(fn ($moduleData) => $moduleData['permissions'])
            ->firstWhere('name', $permissionName) ?: null;
    }

    /**
     * Check if a permission is currently selected
     */
    private function isPermissionSelected(string $permissionName): bool
    {
        return in_array($permissionName, $this->selectedPermissions);
    }

    /**
     * Calculate the current state based on original state and selection
     */
    private function getCurrentState(UserPermissionState $originalState, bool $isSelected): UserPermissionState
    {
        return match ($originalState) {
            UserPermissionState::REVOKED() => $isSelected ? UserPermissionState::DIRECT() : UserPermissionState::REVOKED(),
            UserPermissionState::ROLE() => $isSelected ? UserPermissionState::ROLE() : UserPermissionState::REVOKED(),
            UserPermissionState::DIRECT() => $isSelected ? UserPermissionState::DIRECT() : UserPermissionState::NONE(),
            UserPermissionState::NONE() => $isSelected ? UserPermissionState::DIRECT() : UserPermissionState::NONE(),
            default => UserPermissionState::NONE(),
        };
    }

    /**
     * Toggle all permissions in a module
     */
    public function toggleGroup(string $module): void
    {
        if (! $this->hasModule($module)) {
            return;
        }

        $modulePermissions = $this->getModulePermissions($module);

        if ($this->areAllPermissionsRevoked($modulePermissions)) {
            $this->selectAllRevokedPermissions($modulePermissions);

            return;
        }

        if ($this->areAllInheritedPermissionsSelected($modulePermissions)) {
            $this->revokeAllInheritedPermissions($modulePermissions);

            return;
        }

        if ($this->areAllManageablePermissionsSelected($modulePermissions)) {
            $this->unselectManageablePermissions($modulePermissions);
        } else {
            $this->selectAllManageablePermissions($modulePermissions);
        }
    }

    /**
     * Check if module exists in permission states
     */
    private function hasModule(string $module): bool
    {
        return isset($this->permissionStates[$module]);
    }

    /**
     * Get permissions for a specific module
     */
    private function getModulePermissions(string $module): array
    {
        return $this->permissionStates[$module]['permissions'];
    }

    /**
     * Get all permissions from all modules
     */
    private function getAllPermissions(): Collection
    {
        return collect($this->permissionStates)
            ->flatMap(fn ($moduleData) => $moduleData['permissions']);
    }

    /**
     * Check if all permissions in a module are currently revoked
     */
    private function areAllPermissionsRevoked(array $modulePermissions): bool
    {
        if (empty($modulePermissions)) {
            return false;
        }

        return collect($modulePermissions)
            ->every(fn ($permission) => $this->getCurrentPermissionState($permission['name']) === UserPermissionState::REVOKED()->value
            );
    }

    /**
     * Check if all permissions are originally inherited and currently selected
     */
    private function areAllInheritedPermissionsSelected(array $modulePermissions): bool
    {
        if (empty($modulePermissions)) {
            return false;
        }

        return collect($modulePermissions)
            ->every(fn ($permission) => $permission['state'] === UserPermissionState::ROLE()->value &&
                $permission['checked'] &&
                $this->isPermissionSelected($permission['name'])
            );
    }

    /**
     * Check if all manageable permissions are selected
     */
    private function areAllManageablePermissionsSelected(array $modulePermissions): bool
    {
        $manageablePermissions = $this->getManageablePermissions($modulePermissions);

        if ($manageablePermissions->isEmpty()) {
            return false;
        }

        return $manageablePermissions
            ->every(fn ($permission) => $this->isPermissionSelected($permission['name']));
    }

    /**
     * Get permissions that can be managed (exclude already selected inherited ones)
     */
    private function getManageablePermissions(array $modulePermissions): Collection
    {
        return collect($modulePermissions)
            ->reject(fn ($permission) => $permission['state'] === UserPermissionState::ROLE()->value &&
                $this->isPermissionSelected($permission['name'])
            );
    }

    /**
     * Select all currently revoked permissions
     */
    private function selectAllRevokedPermissions(array $modulePermissions): void
    {
        $permissionNames = collect($modulePermissions)
            ->pluck('name')
            ->reject(fn ($name) => $this->isPermissionSelected($name))
            ->toArray();

        $this->selectedPermissions = array_unique([...$this->selectedPermissions, ...$permissionNames]);
    }

    /**
     * Revoke all inherited permissions
     */
    private function revokeAllInheritedPermissions(array $modulePermissions): void
    {
        $permissionNames = collect($modulePermissions)->pluck('name')->toArray();
        $this->selectedPermissions = array_values(array_diff($this->selectedPermissions, $permissionNames));
    }

    /**
     * Unselect manageable permissions (keep inherited ones)
     */
    private function unselectManageablePermissions(array $modulePermissions): void
    {
        $permissionsToUnselect = collect($modulePermissions)
            ->reject(fn ($permission) => $permission['state'] === UserPermissionState::ROLE()->value &&
                $permission['checked']
            )
            ->pluck('name')
            ->toArray();

        $this->selectedPermissions = array_values(array_diff($this->selectedPermissions, $permissionsToUnselect));
    }

    /**
     * Select all manageable permissions
     */
    private function selectAllManageablePermissions(array $modulePermissions): void
    {
        $permissionsToSelect = collect($modulePermissions)
            ->filter(function ($permission) {
                $permissionName = $permission['name'];
                $originalState = UserPermissionState::make($permission['state']);

                if ($this->isPermissionSelected($permissionName)) {
                    return false;
                }

                return match ($originalState->value) {
                    'role' => $permission['checked'],
                    'revoked', 'direct', 'none' => true,
                    default => false,
                };
            })
            ->pluck('name')
            ->toArray();

        $this->selectedPermissions = array_unique([...$this->selectedPermissions, ...$permissionsToSelect]);
    }

    /**
     * Check if all permissions in a group are fully selected
     */
    public function isGroupFullySelected(string $module): bool
    {
        if (! $this->hasModule($module)) {
            return false;
        }

        $modulePermissions = $this->getModulePermissions($module);

        if (empty($modulePermissions)) {
            return false;
        }

        // All permissions must be visually checked for group to be fully selected
        return collect($modulePermissions)->every(function ($permission) {
            return $this->isPermissionVisuallyChecked($permission['name']);
        });
    }

    /**
     * Check if a permission is effectively selected (visible as selected to user)
     */
    private function isPermissionEffectivelySelected(string $currentState): bool
    {
        return in_array($currentState, [
            UserPermissionState::ROLE()->value,
            UserPermissionState::DIRECT()->value,
        ]);
    }

    /**
     * Check if a permission is visually checked (in UI checkbox)
     */
    private function isPermissionVisuallyChecked(string $permissionName): bool
    {
        $currentState = $this->getCurrentPermissionState($permissionName);
        $isInSelectedArray = in_array($permissionName, $this->selectedPermissions);

        // Visually checked means: in selectedPermissions array AND not revoked
        return $isInSelectedArray && $currentState !== UserPermissionState::REVOKED()->value;
    }

    /**
     * Updated when selectedPermissions changes to ensure reactivity
     */
    public function updatedSelectedPermissions()
    {
        // This method is called every time selectedPermissions array changes
        // We need to ensure group checkboxes reflect the correct state
        $this->validateGroupStatesAfterIndividualChange();
    }

    /**
     * Validate and correct group states after individual permission changes
     */
    private function validateGroupStatesAfterIndividualChange(): void
    {
        foreach ($this->permissionStates as $module => $moduleData) {
            $this->ensureCorrectGroupState($module);
        }
    }

    /**
     * Ensure the correct group state based on individual permission states
     */
    private function ensureCorrectGroupState(string $module): void
    {
        $modulePermissions = $this->getModulePermissions($module);

        if (empty($modulePermissions)) {
            return;
        }

        $permissionStates = $this->analyzeModulePermissionStates($modulePermissions);

        // If all items have the same final state, adjust selectedPermissions to match
        if ($this->shouldForceGroupSelection($permissionStates)) {
            $this->forceSelectAllPermissionsInModule($modulePermissions);
        } elseif ($this->shouldForceGroupDeselection($permissionStates)) {
            $this->forceDeselectAllPermissionsInModule($modulePermissions);
        }
    }

    /**
     * Analyze the current states of all permissions in a module
     */
    private function analyzeModulePermissionStates(array $modulePermissions): array
    {
        $states = [
            'total' => count($modulePermissions),
            'visuallyChecked' => 0,
            'revoked' => 0,
            'inherited' => 0,
            'direct' => 0,
            'none' => 0,
        ];

        foreach ($modulePermissions as $permission) {
            $permissionName = $permission['name'];
            $currentState = $this->getCurrentPermissionState($permissionName);
            $isVisuallyChecked = $this->isPermissionVisuallyChecked($permissionName);

            if ($isVisuallyChecked) {
                $states['visuallyChecked']++;
            }

            switch ($currentState) {
                case UserPermissionState::REVOKED()->value:
                    $states['revoked']++;
                    break;
                case UserPermissionState::ROLE()->value:
                    $states['inherited']++;
                    break;
                case UserPermissionState::DIRECT()->value:
                    $states['direct']++;
                    break;
                case UserPermissionState::NONE()->value:
                    $states['none']++;
                    break;
            }
        }

        return $states;
    }

    /**
     * Determine if we should force all permissions to be selected
     */
    private function shouldForceGroupSelection(array $states): bool
    {
        // If all permissions are inherited or direct (and not revoked),
        // then select-all should be true
        return ($states['inherited'] + $states['direct']) === $states['total'] &&
               $states['revoked'] === 0;
    }

    /**
     * Determine if we should force all permissions to be deselected
     */
    private function shouldForceGroupDeselection(array $states): bool
    {
        // If all permissions are revoked or none, then select-all should be false
        return ($states['revoked'] + $states['none']) === $states['total'];
    }

    /**
     * Force select all permissions in a module (add to selectedPermissions)
     */
    private function forceSelectAllPermissionsInModule(array $modulePermissions): void
    {
        foreach ($modulePermissions as $permission) {
            $permissionName = $permission['name'];
            if (! in_array($permissionName, $this->selectedPermissions)) {
                $this->selectedPermissions[] = $permissionName;
            }
        }
    }

    /**
     * Force deselect all permissions in a module (remove from selectedPermissions)
     */
    private function forceDeselectAllPermissionsInModule(array $modulePermissions): void
    {
        foreach ($modulePermissions as $permission) {
            $permissionName = $permission['name'];
            $index = array_search($permissionName, $this->selectedPermissions);
            if ($index !== false) {
                array_splice($this->selectedPermissions, $index, 1);
            }
        }
    }

    /**
     * Get group selection state that's computed from current selectedPermissions
     */
    public function getGroupSelectionState(string $module): array
    {
        return [
            'isFullySelected' => $this->isGroupFullySelected($module),
            'isPartiallySelected' => $this->isGroupPartiallySelected($module),
        ];
    }

    /**
     * Debug helper to understand group selection state
     */
    public function debugGroupState(string $module): array
    {
        $modulePermissions = $this->getModulePermissions($module);
        $debug = [
            'module' => $module,
            'permissions' => [],
        ];

        foreach ($modulePermissions as $permission) {
            $permissionName = $permission['name'];
            $currentState = $this->getCurrentPermissionState($permissionName);
            $isInSelectedArray = $this->isPermissionSelected($permissionName);
            $isEffective = $this->isPermissionEffectivelySelected($currentState);
            $isVisuallyChecked = $this->isPermissionVisuallyChecked($permissionName);

            $debug['permissions'][] = [
                'name' => $permissionName,
                'currentState' => $currentState,
                'isInSelectedArray' => $isInSelectedArray,
                'isEffective' => $isEffective,
                'isVisuallyChecked' => $isVisuallyChecked,
            ];
        }

        $debug['isFullySelected'] = $this->isGroupFullySelected($module);
        $debug['isPartiallySelected'] = $this->isGroupPartiallySelected($module);
        $debug['stateAnalysis'] = $this->analyzeModulePermissionStates($modulePermissions);
        $debug['shouldForceSelect'] = $this->shouldForceGroupSelection($debug['stateAnalysis']);
        $debug['shouldForceDeselect'] = $this->shouldForceGroupDeselection($debug['stateAnalysis']);

        return $debug;
    }

    /**
     * Check if a group is partially selected
     */
    public function isGroupPartiallySelected(string $module): bool
    {
        if (! $this->hasModule($module)) {
            return false;
        }

        $modulePermissions = $this->getModulePermissions($module);

        if (empty($modulePermissions)) {
            return false;
        }

        $visuallyCheckedCount = collect($modulePermissions)
            ->filter(fn ($permission) => $this->isPermissionVisuallyChecked($permission['name']))
            ->count();

        $totalCount = count($modulePermissions);

        // Partially selected: some but not all permissions are visually checked
        return $visuallyCheckedCount > 0 && $visuallyCheckedCount < $totalCount;
    }

    public function submit()
    {
        try {
            $this->processPermissionUpdates();

            $this->showSuccessNotification();

            return redirect()->route('users.list');

        } catch (\Exception $e) {
            $this->logError($e);
            $this->showErrorNotification();
        }
    }

    /**
     * Process all permission updates
     */
    private function processPermissionUpdates(): void
    {
        $this->getAllPermissions()
            ->filter(fn ($permission) => $this->hasPermissionChanged($permission))
            ->each(fn ($permission) => $this->togglePermission($permission['name']));
    }

    /**
     * Check if a permission has changed from its original state
     */
    private function hasPermissionChanged(array $permission): bool
    {
        $permissionName = $permission['name'];
        $isCurrentlySelected = $this->isPermissionSelected($permissionName);
        $wasSelected = $permission['checked'];

        return $isCurrentlySelected !== $wasSelected;
    }

    /**
     * Toggle a specific permission
     */
    private function togglePermission(string $permissionName): void
    {
        $this->userPermissionService->toggleUserPermission($this->user, $permissionName);
    }

    /**
     * Show success notification
     */
    private function showSuccessNotification(): void
    {
        $this->dispatch('show-notification', [
            'type' => 'success',
            'title' => 'Permissions mises à jour !',
            'message' => "Les permissions ont été mises à jour avec succès pour {$this->user->full_name}.",
            'timer' => 3000,
        ]);
    }

    /**
     * Show error notification
     */
    private function showErrorNotification(): void
    {
        $this->dispatch('show-notification', [
            'type' => 'error',
            'title' => 'Erreur !',
            'message' => 'Une erreur est survenue lors de la mise à jour des permissions.',
            'timer' => 3000,
        ]);
    }

    /**
     * Log error details
     */
    private function logError(\Exception $e): void
    {
        Log::error('Error updating user permissions', [
            'user_id' => $this->user->id,
            'error' => $e->getMessage(),
        ]);
    }

    public function render()
    {
        return view('livewire.user.manage-user-permissions');
    }
}
