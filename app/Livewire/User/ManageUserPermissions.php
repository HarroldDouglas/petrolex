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
        $allPermissions = $this->getAllPermissions();
        
        if ($allPermissions->isEmpty()) {
            $this->selectedPermissions = [];
            return;
        }
        
        $this->selectedPermissions = $allPermissions
            ->filter(fn ($permission) => isset($permission['checked']) && $permission['checked'])
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

        $isCurrentlySelected = in_array($permissionName, $this->selectedPermissions);
        $originalState = UserPermissionState::tryFrom($originalPermission['state']) ?? UserPermissionState::NONE();

        return $this->getCurrentState($originalState, $isCurrentlySelected)->value;
    }

    /**
     * Get the current state based on original state and selection
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
     * Find the original permission data by name
     */
    private function findOriginalPermission(string $permissionName): ?array
    {
        return collect($this->permissionStates)
            ->flatMap(fn ($moduleData) => $moduleData['permissions'])
            ->firstWhere('name', $permissionName) ?: null;
    }

    /**
     * Get all permissions from all modules
     */
    private function getAllPermissions(): Collection
    {
        if (empty($this->permissionStates)) {
            return collect([]);
        }
        
        return collect($this->permissionStates)
            ->flatMap(fn ($moduleData) => $moduleData['permissions'] ?? []);
    }

    /**
     * Toggle all permissions in a module
     */
    public function toggleGroup(string $module): void
    {
        if (! isset($this->permissionStates[$module])) {
            return;
        }

        $modulePermissions = $this->permissionStates[$module]['permissions'];
        $permissionNames = collect($modulePermissions)->pluck('name')->toArray();
        
        $allSelected = collect($permissionNames)->every(fn ($name) => in_array($name, $this->selectedPermissions));
        
        if ($allSelected) {
            $this->selectedPermissions = array_values(array_diff($this->selectedPermissions, $permissionNames));
        } else {
            $this->selectedPermissions = array_unique([...$this->selectedPermissions, ...$permissionNames]);
        }
    }

    /**
     * Check if all permissions in a group are selected
     */
    public function isGroupFullySelected(string $module): bool
    {
        if (! isset($this->permissionStates[$module])) {
            return false;
        }

        $modulePermissions = $this->permissionStates[$module]['permissions'];
        $permissionNames = collect($modulePermissions)->pluck('name');

        return $permissionNames->every(fn ($name) => in_array($name, $this->selectedPermissions));
    }

    /**
     * Check if a group is partially selected
     */
    public function isGroupPartiallySelected(string $module): bool
    {
        if (! isset($this->permissionStates[$module])) {
            return false;
        }

        $modulePermissions = $this->permissionStates[$module]['permissions'];
        $permissionNames = collect($modulePermissions)->pluck('name');
        
        $selectedCount = $permissionNames->filter(fn ($name) => in_array($name, $this->selectedPermissions))->count();
        
        return $selectedCount > 0 && $selectedCount < $permissionNames->count();
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
     * Check if a permission should be visually checked (for UI)
     */
    public function isPermissionChecked(string $permissionName): bool
    {
        $currentState = $this->getCurrentPermissionState($permissionName);
        
        return in_array($currentState, [
            UserPermissionState::ROLE()->value,
            UserPermissionState::DIRECT()->value,
        ]);
    }

    /**
     * Submit form - process all permission changes
     */
    public function submit()
    {
        try {
            $this->getAllPermissions()
                ->filter(fn ($permission) => $this->hasPermissionChanged($permission))
                ->each(fn ($permission) => $this->userPermissionService->toggleUserPermission($this->user, $permission['name']));

            $this->dispatch('show-notification', [
                'type' => 'success',
                'title' => 'Permissions mises à jour !',
                'message' => "Les permissions ont été mises à jour avec succès pour {$this->user->full_name}.",
                'timer' => 3000,
            ]);

            return redirect()->route('users.list');

        } catch (\Exception $e) {
            Log::error('Error updating user permissions', [
                'user_id' => $this->user->id,
                'error' => $e->getMessage(),
            ]);
            
            $this->dispatch('show-notification', [
                'type' => 'error',
                'title' => 'Erreur !',
                'message' => 'Une erreur est survenue lors de la mise à jour des permissions.',
                'timer' => 3000,
            ]);
        }
    }

    /**
     * Check if a permission has changed from its original state
     */
    private function hasPermissionChanged(array $permission): bool
    {
        $isCurrentlySelected = in_array($permission['name'], $this->selectedPermissions);
        
        return $isCurrentlySelected !== $permission['checked'];
    }

    public function render()
    {
        return view('livewire.user.manage-user-permissions');
    }
}