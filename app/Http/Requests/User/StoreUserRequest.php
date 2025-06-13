<?php

namespace App\Http\Requests\User;

class StoreUserRequest extends BaseUserRequest
{
    public function authorize(): bool
    {
        return true;
    }
}
