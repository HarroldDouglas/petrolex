<?php

namespace App\Http\Requests\User;

use Illuminate\Validation\Rule;

class StoreUserRequest extends BaseUserRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = parent::rules();

        // Remove email confirmation validation for web interface
        // Confirmation is only required for API endpoints (handled in StoreCustomerRequest)
        $rules['email'] = [
            'required',
            'email',
            'max:255',
            Rule::unique('users', 'email'),
        ];

        return $rules;
    }
}
