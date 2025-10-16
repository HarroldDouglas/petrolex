<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class OrangeCallbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|string',
            'payToken' => 'sometimes|string|nullable',
            'message' => 'sometimes|string|nullable',
            'txnid' => 'sometimes|string|nullable',
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => '📊 Orange callback status is required',
            'status.string' => '📊 Orange callback status must be a string',
            'payToken.string' => '🎫 Pay token must be a string',
            'txnid.string' => '🆔 Transaction ID must be a string',
        ];
    }
}
