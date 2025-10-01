<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class BaseRoleRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'guard_name' => 'sometimes|string|in:web,api',
            'selectedPermissions' => 'sometimes|array',
            'selectedPermissions.*' => 'string|exists:permissions,name'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Le nom du rôle est requis.',
            'name.string' => 'Le nom du rôle doit être une chaîne de caractères.',
            'name.max' => 'Le nom du rôle ne doit pas dépasser 255 caractères.',
            'guard_name.string' => 'Le guard doit être une chaîne de caractères.',
            'guard_name.in' => 'Le guard doit être soit "web" soit "api".',
            'selectedPermissions.array' => 'Les permissions doivent être un tableau.',
            'selectedPermissions.*.string' => 'Chaque permission doit être une chaîne de caractères.',
            'selectedPermissions.*.exists' => 'Une ou plusieurs permissions sélectionnées n\'existent pas.',
        ];
    }

    /**
     * Get the unique validation rule for role name
     */
    protected function getNameUniqueRule(): \Illuminate\Validation\Rules\Unique
    {
        return Rule::unique('roles', 'name')
            ->where(function ($query) {
                $query->where('guard_name', request()->input('guard_name', 'web'));
            });
    }
}