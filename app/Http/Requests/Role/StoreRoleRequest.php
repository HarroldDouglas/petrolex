<?php

namespace App\Http\Requests\Role;

use Illuminate\Validation\Rule;

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
        $rules['name'][] = Rule::unique('roles', 'name')
            ->where('guard_name', $this->input('guard_name', 'web'));

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
