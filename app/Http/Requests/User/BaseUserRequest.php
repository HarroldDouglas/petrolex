<?php

namespace App\Http\Requests\User;

use App\Enums\Language;
use App\Enums\UserRole;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class BaseUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],
            'phone_number' => [
                'required',
                'string',
                'min:8',
                'max:15',
                Rule::unique('users', 'phone_number')->where(function ($query) {
                    return $query->where('country_id', $this->input('country_id'));
                }),
            ],
            'country_id' => ['required', 'integer', 'exists:countries,id'],
            'password' => ['required', 'string', 'min:8'],
            'language' => ['nullable', Rule::in(Language::getValues())],
            'role' => ['required', Rule::in(UserRole::values())],
            'image' => [
                'nullable',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (is_string($value)) {
                        return;
                    }

                    if (! is_a($value, \Illuminate\Http\UploadedFile::class)) {
                        $fail('L\'image doit être un fichier image valide.');
                    }
                },
                'max:2048',
            ],
            'distribution_center_ids' => ['nullable', 'array'],
            'distribution_center_ids.*' => [
                Rule::requiredIf(function () {
                    $role = $this->input('role');

                    return in_array($role, [
                        UserRole::CENTER_MANAGER()->value,
                        UserRole::DELIVERY_PERSON()->value,
                    ]);
                }),
                'exists:distribution_centers,id',
            ],
            'is_active' => ['required', 'boolean'],
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
            'phone_number.required' => 'Le téléphone est obligatoire.',
            'phone_number.string' => 'Le téléphone doit être une chaîne de caractères.',
            'phone_number.unique' => 'Ce numéro de téléphone est déjà utilisé.',
            'phone_number.max' => 'Le téléphone ne doit pas dépasser 20 caractères.',
            'country_id.required' => 'Le pays est obligatoire.',
            'country_id.integer' => 'Le pays doit être un identifiant valide.',
            'country_id.exists' => 'Le pays sélectionné n\'existe pas.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.string' => 'Le mot de passe doit être une chaîne de caractères.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'language.in' => 'La langue sélectionnée n\'est pas valide.',
            'role.enum' => 'Le rôle sélectionné n\'est pas valide.',
            'role.required' => 'Le rôle est obligatoire.',
            'image.image' => 'L\'image doit être un fichier image valide.',
            'image.max' => 'L\'image ne doit pas dépasser 2 Mo.',
            'distribution_center_ids.array' => 'Les centres de distribution doivent être un tableau.',
            'distribution_center_ids.*.exists' => 'Un ou plusieurs centres de distribution sélectionnés n\'existent pas.',
            'is_active.required' => 'Le statut est obligatoire.',
        ];
    }
}
