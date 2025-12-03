<?php

namespace App\Http\Requests\Customer;

use App\Rules\CountryPhoneRule;
use Illuminate\Foundation\Http\FormRequest;

class AbstractCustomerDeliveryAddressRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        // Use user's country_code for phone validation
        // since phone_country_code is a dial code (+237) not an ISO code (CM)
        $countryCode = $this->user()?->country_code ?? 'CM';

        return [
            'label' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'neighborhood_id' => ['required', 'integer', 'exists:neighborhoods,id'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'phone' => [
                'nullable',
                'string',
                'max:20',
                new CountryPhoneRule($countryCode),
            ],
            'phone_country_code' => ['nullable', 'string', 'max:10'],
            'contact_firstname' => ['nullable', 'string', 'max:255'],
            'contact_lastname' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'address_precision' => ['nullable', 'string'],
            'is_default' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'label.required' => __('validation.delivery_address.label_required'),
            'address.required' => __('validation.delivery_address.address_required'),
            'neighborhood_id.required' => __('validation.delivery_address.neighborhood_required'),
            'neighborhood_id.exists' => __('validation.delivery_address.neighborhood_invalid'),
            'email.email' => __('validation.delivery_address.email_invalid'),
            'phone.max' => __('validation.delivery_address.phone_max'),
        ];
    }
}
