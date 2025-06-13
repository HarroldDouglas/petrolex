<?php

namespace App\Http\Requests\Bottletype;

class StoreBottleTypeRequest extends BaseBottleTypeRequest
{
    public function authorize(): bool
    {
        return true;
    }
}
