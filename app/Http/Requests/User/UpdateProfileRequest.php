<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Enums\Language;
use App\Rules\CountryPhoneRule;
use App\Rules\UniquePhoneByCountryRule;
use App\Rules\ValidISOCountryRule;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function __construct() {}

    public function authorize(): bool
    {
        return true;
    }

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
        $userId = auth()->id();

        $countryCode = $this->input('country_code', '');

        return [
            'first_name' => ['sometimes', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'phone_number' => [
                'sometimes',
                'string',
                'max:20',
                new CountryPhoneRule($countryCode),
                new UniquePhoneByCountryRule($countryCode, $userId),
            ],
            'country_code' => ['sometimes', 'string', 'size:2', 'regex:/^[A-Za-z]{2}$/', new ValidISOCountryRule],
            'password' => ['nullable', 'string', 'min:8'],
            'language' => ['nullable', Rule::in(Language::getValues())],
            'image' => [
                'nullable',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (is_string($value)) {
                        return;
                    }

                    if (! is_a($value, UploadedFile::class)) {
                        $fail('L\'image doit être un fichier image valide.');
                    }
                },
                'max:2048',
            ],
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
            'phone_number.max' => 'Le téléphone ne doit pas dépasser 20 caractères.',
            'language.in' => 'La langue sélectionnée n\'est pas valide.',
            'image.image' => 'L\'image doit être un fichier image valide.',
            'image.max' => 'L\'image ne doit pas dépasser 2 Mo.',
        ];
    }
}
