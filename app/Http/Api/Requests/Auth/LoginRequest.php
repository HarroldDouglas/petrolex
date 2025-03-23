<?php

namespace App\Http\Api\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => 'L\'adresse e-mail est obligatoire',
            'email.email' => 'Veuillez fournir une adresse e-mail valide',
            'password.required' => 'Le mot de passe est obligatoire',
        ];
    }

    /**
     * Get credentials for authentication attempt.
     */
    public function credentials(): array
    {
        return $this->only('email', 'password');
    }
}
