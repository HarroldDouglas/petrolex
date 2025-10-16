<?php

namespace App\Livewire\Role;

use App\Http\Requests\Role\BaseRoleRequest;
use App\Http\Requests\Role\StoreRoleRequest;

class CreateRoleForm extends AbstractRoleForm
{
    public function mount()
    {
        $this->initialize();
    }

    protected function customRequest(): BaseRoleRequest
    {
        return new StoreRoleRequest;
    }

    public function submit()
    {
        $validatedData = $this->validate();

        $data = [
            'name' => $validatedData['name'],
            'guard_name' => $validatedData['guard_name'] ?? 'web',
            'permissions' => $validatedData['selectedPermissions'] ?? [],
        ];

        $this->roleService->create($data);

        session()->flash('success', 'Rôle créé avec succès!');

        return redirect()->route('roles.list');
    }
}
