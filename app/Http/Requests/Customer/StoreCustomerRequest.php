<?php

namespace App\Http\Requests\Customer;

use App\Http\Requests\User\BaseUserRequest;

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
        unset($rules['is_active']);
        unset($rules['address']);

        return $rules;
    }

    public function messages(): array
    {
        $messages = parent::messages();

        return $messages;
    }
}
