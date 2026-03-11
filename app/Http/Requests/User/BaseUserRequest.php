<?php

namespace App\Http\Requests\User;

use App\Enums\Language;
use App\Enums\UserRole;
use App\Rules\CountryPhoneRule;
use App\Rules\UniquePhoneByCountryRule;
use App\Rules\ValidISOCountryRule;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class BaseUserRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $phone = $this->input('phone_number');
        $countryCode = $this->input('country_code', '');

        if ($phone && $countryCode) {
            $phoneCode = \App\Models\Geography\Country::where('code', strtoupper($countryCode))->value('phone_code');
            if ($phoneCode && str_starts_with($phone, $phoneCode)) {
                $this->merge(['phone_number' => substr($phone, strlen($phoneCode))]);
            }
        }
    }

    public function rules(): array
    {
        $countryCode = $this->input('country_code', '');

        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                'confirmed',
                Rule::unique('users', 'email'),
            ],
            'phone_number' => [
                'required',
                'string',
                new CountryPhoneRule($countryCode),
                new UniquePhoneByCountryRule($countryCode),
            ],
            'country_code' => ['required', 'string', 'size:2', 'regex:/^[A-Za-z]{2}$/', new ValidISOCountryRule],
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
            'email.confirmed' => 'La confirmation de l\'email ne correspond pas.',
            'phone_number.required' => 'Le téléphone est obligatoire.',
            'phone_number.string' => 'Le téléphone doit être une chaîne de caractères.',
            'phone_number.unique' => 'Ce numéro de téléphone est déjà utilisé.',
            'phone_number.max' => 'Le téléphone ne doit pas dépasser 20 caractères.',
            'country_code.required' => 'Le code pays est obligatoire.',
            'country_code.string' => 'Le code pays doit être une chaîne de caractères.',
            'country_code.size' => 'Le code pays doit contenir exactement 2 caractères (ex: CM, FR).',
            'country_code.regex' => 'Le code pays doit être au format ISO (2 lettres majuscules, ex: CM, FR, US).',
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
