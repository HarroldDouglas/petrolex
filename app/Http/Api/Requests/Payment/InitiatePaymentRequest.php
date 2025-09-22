<?php

declare(strict_types=1);

namespace App\Http\Api\Requests\Payment;

use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class InitiatePaymentRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert payment_method to lowercase for case-insensitive validation
        if ($this->has('payment_method')) {
            $this->merge(['payment_method' => strtolower($this->input('payment_method'))]);
        }
    }

    public function rules(): array
    {
        $paymentMethod = $this->input('payment_method');

        $rules = [
            'payment_method' => ['required', 'string', Rule::in(PaymentMethod::values())],
        ];

        if ($paymentMethod === PaymentMethod::ORANGE_MONEY()->value || $paymentMethod === PaymentMethod::MTN_MONEY()->value) {
            $rules['payment_details.phone'] = ['required', 'string', 'regex:/^[0-9]{8,15}$/'];
        } elseif ($paymentMethod === PaymentMethod::CREDIT_CARD()->value) {
            $rules['payment_details.card_number'] = ['required', 'string', 'regex:/^[0-9]{13,19}$/'];
            $rules['payment_details.cvv'] = ['required', 'string', 'regex:/^[0-9]{3,4}$/'];
            $rules['payment_details.expiry_date'] = ['required', 'date_format:m/y', 'after:today'];
            $rules['payment_details.cardholder_name'] = ['required', 'string', 'max:255'];
        }

        return $rules;
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->input('payment_method') === PaymentMethod::CREDIT_CARD()->value) {
                $this->validateCreditCard($validator);
            }
        });
    }

    public function authorize(): bool
    {
        return $this->user() && $this->user()->customer;
    }

    public function messages(): array
    {
        return [
            'payment_method.required' => __('validation.payment.payment_method_required'),
            'payment_method.in' => __('validation.payment.payment_method_invalid'),
            'payment_details.phone.required' => __('validation.payment.phone_required'),
            'payment_details.phone.regex' => __('validation.payment.phone_format'),
            'payment_details.card_number.required' => __('validation.payment.card_number_required'),
            'payment_details.card_number.regex' => __('validation.payment.card_number_format'),
            'payment_details.cvv.required' => __('validation.payment.cvv_required'),
            'payment_details.cvv.regex' => __('validation.payment.cvv_format'),
            'payment_details.expiry_date.required' => __('validation.payment.expiry_date_required'),
            'payment_details.expiry_date.date_format' => __('validation.payment.expiry_date_format'),
            'payment_details.expiry_date.after' => __('validation.payment.expiry_date_future'),
            'payment_details.cardholder_name.required' => __('validation.payment.cardholder_name_required'),
        ];
    }

    private function validateCreditCard($validator): void
    {
        $cardNumber = str_replace(' ', '', $this->input('payment_details.card_number', ''));

        if (! $this->luhnCheck($cardNumber)) {
            $validator->errors()->add('payment_details.card_number',
                __('validation.payment.invalid_card_number')
            );
        }
    }

    private function luhnCheck(string $number): bool
    {
        $sum = 0;
        $length = strlen($number);

        for ($i = $length - 1; $i >= 0; $i--) {
            $digit = (int) $number[$i];
            if (($length - $i) % 2 === 0) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            $sum += $digit;
        }

        return $sum % 10 === 0;
    }
}
