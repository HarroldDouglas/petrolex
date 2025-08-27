<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ResendOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email|exists:users,email',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'L\'adresse mail est requise.',
            'email.email' => 'Veuillez fournir une adresse mail valide.',
            'email.exists' => 'Aucun utilisateur trouvé avec cette adresse mail.',
        ];
    }
}
