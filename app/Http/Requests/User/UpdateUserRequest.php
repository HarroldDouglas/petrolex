<?php

namespace App\Http\Requests\User;

use Illuminate\Validation\Rule;

class UpdateUserRequest extends BaseUserRequest
{
    /**
     * Constructor
     */
    public function __construct(protected $id = null)
    {
        parent::__construct();
    }

    public function withId($id): static
    {
        $this->id = $id;

        return $this;
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
                'phone_number' => [
                    'required',
                    'string',
                    'max:20',
                    Rule::unique('users', 'phone_number')->ignore($this->id),
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
