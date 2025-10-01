<?php

namespace App\Http\Requests\Role;

class StoreRoleRequest extends BaseRoleRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('roles.create');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = parent::rules();
        
        // Add unique constraint for creating new roles
        $rules['name'][] = $this->getNameUniqueRule();
        
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