<?php

namespace App\Http\Requests\Bottletype;

use Illuminate\Validation\Rule;

class UpdateBottleTypeRequest extends BaseBottleTypeRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge(
            [
                'id' => ['required', 'integer', 'exists:bottle_types,id'],
            ],
            parent::rules()
        );
    }

    protected function nameRules(): array
    {
        // To Do : ensure name is unique except for the current bottle type being updated
        return [
            'required',
            'string',
            'max:255',
            //Rule::unique('bottle_types', 'name')->ignore($this->name),
        ];
    }
}
