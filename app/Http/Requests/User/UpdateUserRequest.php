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

    protected function passwordRules(): array
    {
        return ['nullable', 'string', 'min:8'];
    }

    protected function emailRules(): array
    {
        return [
            'required',
            'email',
            'max:255',
            Rule::unique('users', 'email')->ignore($this->id),
        ];
    }

    public function rules(): array
    {
        return array_merge(
            [
                'id' => ['required', 'integer', 'exists:users,id'],
            ],
            parent::rules()
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
