<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

final class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'old_password' => [
                'required',
                'string',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (! Hash::check($value, Auth::user()->password)) {
                        $fail('L\'ancien mot de passe est incorrect.');
                    }
                },
            ],
            'new_password' => 'required|string|min:8|confirmed',
            'new_password_confirmation' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'old_password.required' => 'L\'ancien mot de passe est obligatoire.',
            'new_password.required' => 'Le nouveau mot de passe est obligatoire.',
            'new_password.min' => 'Le nouveau mot de passe doit contenir au moins 8 caractères.',
            'new_password.confirmed' => 'La confirmation du nouveau mot de passe ne correspond pas.',
            'new_password_confirmation.required' => 'La confirmation du nouveau mot de passe est obligatoire.',
        ];
    }
}
