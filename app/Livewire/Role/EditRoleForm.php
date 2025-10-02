<?php

namespace App\Livewire\Role;

use App\Http\Requests\Role\UpdateRoleRequest;
use App\Http\Requests\Role\BaseRoleRequest;
use Illuminate\Support\Facades\Log;
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

        // Log what we're submitting
        Log::info('EditRoleForm: Submitting role update', [
            'role_id' => $this->role->id,
            'validated_data' => $validatedData,
            'final_data' => $data,
            'selected_permissions_count' => count($data['permissions'])
        ]);

        $this->roleService->update($this->role, $data);

        session()->flash('success', 'Rôle modifié avec succès!');
        
        return redirect()->route('roles.list');
    }
}