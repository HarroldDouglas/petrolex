<?php

namespace App\Http\Requests\Role;

use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UpdateRoleRequest extends BaseRoleRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('roles.edit');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = parent::rules();
        
        // Add unique constraint for updating roles (ignore current role)
        $role = $this->route('role');
        if ($role instanceof Role) {
            $rules['name'][] = Rule::unique('roles', 'name')
                ->ignore($role->id)
                ->where('guard_name', $role->guard_name);
        } else {
            $rules['name'][] = $this->getNameUniqueRule();
        }
        
        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'name.unique' => 'Ce nom de rôle existe déjà.',
        ]);
    }
}