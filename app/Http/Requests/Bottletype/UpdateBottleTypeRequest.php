<?php

namespace App\Http\Requests\Bottletype;

use Illuminate\Validation\Rule;

class UpdateBottleTypeRequest extends BaseBottleTypeRequest
{
    /**
     * Constructor
     */
    public function __construct(protected $bottleTypeId)
    {
        parent::__construct();
        $this->bottleTypeId = $bottleTypeId;
    }

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
        return [
            'required',
            'string',
            'max:255',
            Rule::unique('bottle_types', 'name')
                ->ignore($this->bottleTypeId),
        ];
    }
}
