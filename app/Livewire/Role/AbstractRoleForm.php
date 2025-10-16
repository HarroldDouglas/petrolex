<?php

namespace App\Livewire\Role;

use App\Http\Requests\Role\BaseRoleRequest;
use App\Services\Role\RoleService;
use Livewire\Component;
use Spatie\Permission\Models\Role;

abstract class AbstractRoleForm extends Component
{
    public ?Role $role = null;
    public string $name = '';
    public string $guard_name = 'web';
    public array $selectedPermissions = [];
    public array $availablePermissions = [];

    protected RoleService $roleService;

    public function boot(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    public function initialize()
    {
        $this->loadAvailablePermissions();

        if ($this->role) {
            $this->name = $this->role->name;
            $this->guard_name = $this->role->guard_name;
            $this->selectedPermissions = $this->role->permissions->pluck('name')->toArray();
        }
    }

    protected function loadAvailablePermissions()
    {
        $this->availablePermissions = $this->roleService->getAvailablePermissions();
    }

    public function toggleGroup(string $module)
    {
        $modulePermissions = collect($this->availablePermissions[$module]['permissions'])
            ->pluck('name')
            ->toArray();

        $selectedInModule = array_intersect($this->selectedPermissions, $modulePermissions);

        if (count($selectedInModule) === count($modulePermissions)) {
            $this->selectedPermissions = array_diff($this->selectedPermissions, $modulePermissions);
        } else {
            $this->selectedPermissions = array_unique(array_merge($this->selectedPermissions, $modulePermissions));
        }
    }

    public function isGroupFullySelected(string $module): bool
    {
        if (! isset($this->availablePermissions[$module])) {
            return false;
        }
        // TODO when the last revoked permission of a group is revoked, the select all item shoud be unchecked, the
        $modulePermissions = collect($this->availablePermissions[$module]['permissions'])
            ->pluck('name')
            ->toArray();

        return count(array_intersect($this->selectedPermissions, $modulePermissions)) === count($modulePermissions);
    }

    public function isGroupPartiallySelected(string $module): bool
    {
        if (! isset($this->availablePermissions[$module])) {
            return false;
        }

        $modulePermissions = collect($this->availablePermissions[$module]['permissions'])
            ->pluck('name')
            ->toArray();

        $selectedInModule = array_intersect($this->selectedPermissions, $modulePermissions);

        return count($selectedInModule) > 0 && count($selectedInModule) < count($modulePermissions);
    }

    abstract protected function customRequest(): BaseRoleRequest;

    protected function rules(): array
    {
        return $this->customRequest()->rules();
    }

    protected function messages(): array
    {
        return array_merge($this->customRequest()->messages(), [
            'selectedPermissions.*.exists' => 'Une ou plusieurs permissions sélectionnées n\'existent pas.',
        ]);
    }

    public function render()
    {
        return view('livewire.role.role-form');
    }
}
