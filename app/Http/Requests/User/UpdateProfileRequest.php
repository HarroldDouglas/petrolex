<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

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

    public function rules(): array
    {
        $userId = auth()->id();
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'phone_number' => [
                'required', 
                'string', 
                'max:20',
                Rule::unique('users', 'phone_number')->ignore($userId)
            ],
            'password' => ['nullable', 'string', 'min:8'],
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
            'image.image' => 'L\'image doit être un fichier image valide.',
            'image.max' => 'L\'image ne doit pas dépasser 2 Mo.',
        ];
    }
}
