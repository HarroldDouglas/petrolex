<?php

namespace App\Livewire\Role;

use App\Http\Requests\Role\StoreRoleRequest;
use Illuminate\Foundation\Http\FormRequest;

class CreateRoleForm extends AbstractRoleForm
{
    public function mount()
    {
        $this->initialize();
    }

    protected function customRequest(): FormRequest
    {
        return new StoreRoleRequest;
    }

    public function submit()
    {
        // Map selectedPermissions to permissions for validation
        $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9àâäçéèêëïîôöùûüÿñæœ\s\-_]+$/u',
                \Illuminate\Validation\Rule::unique('roles', 'name')->where('guard_name', $this->guard_name)
            ],
            'guard_name' => 'sometimes|string|in:web,api',
            'selectedPermissions' => 'sometimes|array',
            'selectedPermissions.*' => 'string|exists:permissions,name'
        ], [
            'name.required' => 'Le nom du rôle est requis.',
            'name.unique' => 'Ce nom de rôle existe déjà.',
            'name.regex' => 'Le nom du rôle ne peut contenir que des lettres, chiffres, espaces, tirets et underscores.',
            'selectedPermissions.*.exists' => 'Une ou plusieurs permissions sélectionnées n\'existent pas.',
        ]);

        $data = [
            'name' => $this->name,
            'guard_name' => $this->guard_name,
            'permissions' => $this->selectedPermissions ?? [],
        ];

        $this->roleService->create($data);

        session()->flash('success', 'Rôle créé avec succès!');
        
        return redirect()->route('roles.list');
    }
}