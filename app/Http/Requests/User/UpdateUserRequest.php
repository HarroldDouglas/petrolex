<?php

namespace App\Http\Requests\User;

use Illuminate\Validation\Rule;

class UpdateUserRequest extends BaseUserRequest
{
    /**
     * Constructor
     */
    public function __construct(protected $id)
    {
        parent::__construct();
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge(
            parent::rules(),
            [
                'id' => ['required', 'integer', 'exists:users,id'],
                'email' => [
                    'required',
                    'email',
                    'max:255',
                    Rule::unique('users', 'email')->ignore($this->id),
                ],
                'password' => ['nullable', 'string', 'min:8'],
            ]
        );
    }

    public function messages(): array
    {
        return array_merge(
            [
                'id.required' => 'L\'id est obligatoire',
            ],
            parent::messages()
        );
    }
}
