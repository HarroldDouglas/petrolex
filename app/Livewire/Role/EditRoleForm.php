<?php

namespace App\Livewire\Role;

use App\Http\Requests\Role\BaseRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use Spatie\Permission\Models\Role;

class EditRoleForm extends AbstractRoleForm
{
    public function mount(Role $role)
    {
        $this->role = $role;
        $this->initialize();
    }

    protected function customRequest(): BaseRoleRequest
    {
        return new UpdateRoleRequest($this->role->id);
    }

    public function submit()
    {
        $validatedData = $this->validate();

        $data = [
            'name' => $validatedData['name'],
            'guard_name' => $validatedData['guard_name'] ?? $this->role->guard_name,
            'permissions' => $validatedData['selectedPermissions'] ?? [],
        ];

        $this->roleService->update($this->role, $data);

        session()->flash('success', 'Rôle modifié avec succès!');

        return redirect()->route('roles.list');
    }
}
