<?php

namespace App\Http\Requests\Role;

use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UpdateRoleRequest extends BaseRoleRequest
{
    protected $roleId;

    public function __construct($roleId = null)
    {
        parent::__construct();
        $this->roleId = $roleId;
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = parent::rules();

        // Add unique constraint for role name, ignoring the current role being updated
        $rules['name'][] = Rule::unique('roles', 'name')
            ->ignore($this->roleId);

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
