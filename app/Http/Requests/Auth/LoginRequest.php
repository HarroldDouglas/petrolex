<?php

namespace App\Http\Requests\Auth;

use App\Rules\ValidISOCountryRule;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'login' => 'required|string',
            'password' => 'required|string',
            'country_code' => ['nullable', 'string', 'size:2', 'regex:/^[A-Za-z]{2}$/', new ValidISOCountryRule],
        ];

        // Si le login ressemble à un numéro de téléphone, country_code devient obligatoire
        if ($this->isPhoneNumber($this->input('login'))) {
            $rules['country_code'][0] = 'required';
        }

        return $rules;
    }

    private function isPhoneNumber(?string $login): bool
    {
        if (empty($login)) {
            return false;
        }

        // Vérifier si c'est un numéro de téléphone (contient uniquement des chiffres, espaces, +, -, ())
        return preg_match('/^[\d\s\+\-\(\)]+$/', $login) === 1;
    }
}
