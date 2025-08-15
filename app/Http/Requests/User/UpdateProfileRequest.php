<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = auth()->id();

        return [
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'phone_number' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('users', 'phone_number')->ignore($userId),
            ],
            'password' => ['nullable', 'string', 'min:8'],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'Le prénom est obligatoire.',
            'last_name.required' => 'Le nom de famille est obligatoire.',
            'email.required' => 'L\'email est obligatoire.',
            'phone_number.required' => 'Le numéro de téléphone est obligatoire.',
            'password.required' => 'Le mot de passe est obligatoire.',
        ];
    }
}
