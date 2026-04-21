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
            'phone' => [
                'required',
                'string',
                'max:20',
                new CountryPhoneRule($countryCode),
            ],
            'latitude' => ['nullable', 'numeric', 'required_without:location_link'],
            'longitude' => ['nullable', 'numeric', 'required_without:location_link'],
            'location_link' => ['nullable', 'string', 'url', 'max:2048', 'required_without:latitude'],
            'address' => ['nullable', 'string', 'max:255'],
            'neighborhood_id' => ['nullable', 'integer', 'exists:neighborhoods,id'],
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
            'phone.required' => __('validation.delivery_address.phone_required'),
            'latitude.required_without' => __('validation.delivery_address.coordinates_or_link_required'),
            'longitude.required_without' => __('validation.delivery_address.coordinates_or_link_required'),
            'location_link.required_without' => __('validation.delivery_address.coordinates_or_link_required'),
            'location_link.url' => __('validation.delivery_address.location_link_invalid'),
            'neighborhood_id.exists' => __('validation.delivery_address.neighborhood_invalid'),
            'email.email' => __('validation.delivery_address.email_invalid'),
            'phone.max' => __('validation.delivery_address.phone_max'),
        ];
    }
}
