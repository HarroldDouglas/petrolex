<?php

namespace App\Http\Requests\PaymentTest;

use App\Services\PaymentTest\PaymentTestConstants;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class PaymentTestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'phone_number' => [
                'required',
                'string',
                'min:9',
                'max:15'
            ],
            'amount' => [
                'required',
                'numeric',
                'min:10',
                'max:1000000'
            ],
            'test_mode' => [
                'sometimes',
                'string',
                'in:' . PaymentTestConstants::MODE_SANDBOX . ',' . PaymentTestConstants::MODE_LIVE
            ],
            'external_id' => [
                'sometimes',
                'nullable',
                'string',
                'max:50'
            ],
            'reference' => [
                'sometimes',
                'nullable',
                'string',
                'max:100'
            ]
        ];
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [
            'phone_number.required' => '📱 Phone number is required',
            'phone_number.min' => '📱 Phone number must be at least 9 characters',
            'phone_number.max' => '📱 Phone number cannot exceed 15 characters',
            'amount.required' => '💰 Amount is required',
            'amount.numeric' => '💰 Amount must be a valid number',
            'amount.min' => '💰 Minimum amount is 10 FCFA',
            'amount.max' => '💰 Maximum amount is 1,000,000 FCFA',
            'test_mode.in' => '🔧 Test mode must be either "sandbox" or "live"',
            'external_id.max' => '🆔 External ID cannot exceed 50 characters',
            'reference.max' => '📝 Reference cannot exceed 100 characters'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'phone_number' => 'phone number',
            'external_id' => 'external ID',
            'test_mode' => 'test mode'
        ];
    }


}