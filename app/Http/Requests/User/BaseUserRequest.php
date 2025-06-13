<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

abstract class BaseUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'phone' => 'required|string|max:20',
            'role' => 'required|exists:roles,id',
            'distribution_centers' => 'nullable|array',
            'distribution_centers.*' => 'exists:distribution_centers,id'
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'Le prénom est obligatoire.',
            'first_name.string' => 'Le prénom doit être une chaîne de caractères.',
            'first_name.max' => 'Le prénom ne doit pas dépasser 255 caractères.',
            'last_name.required' => 'Le nom est obligatoire.',
            'last_name.string' => 'Le nom doit être une chaîne de caractères.',
            'last_name.max' => 'Le nom ne doit pas dépasser 255 caractères.',
            'email.required' => 'L\'email est obligatoire.',
            'email.email' => 'L\'email doit être une adresse email valide.',
            'email.unique' => 'Cet email est déjà utilisé.',
            'email.max' => 'L\'email ne doit pas dépasser 255 caractères.',
            'phone.required' => 'Le téléphone est obligatoire.',
            'phone.string' => 'Le téléphone doit être une chaîne de caractères.',
            'phone.max' => 'Le téléphone ne doit pas dépasser 20 caractères.',
            'role.required' => 'Le rôle est obligatoire.',
            'role.exists' => 'Le rôle sélectionné n\'existe pas.',
            'distribution_centers.array' => 'Les centres de distribution doivent être un tableau.',
            'distribution_centers.*.exists' => 'Un ou plusieurs centres de distribution sélectionnés n\'existent pas.'
        ];
    }
}
