<?php

namespace App\Http\Requests\Customer;

use App\Http\Requests\User\BaseUserRequest;
use Illuminate\Validation\Rule;

class StoreCustomerRequest extends BaseUserRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = parent::rules();

        unset($rules['role']);

        $rules['current_balance'] = ['nullable', 'numeric', 'min:0'];

        return $rules;
    }

    public function messages(): array
    {
        $messages = parent::messages();

        return $messages;
    }
}
