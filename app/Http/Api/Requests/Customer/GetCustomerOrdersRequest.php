<?php

declare(strict_types=1);

namespace App\Http\Api\Requests\Customer;

use App\Enums\DeliveryType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;

final class GetCustomerOrdersRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('status')) {
            $this->merge(['status' => strtolower($this->input('status'))]);
        }

        if ($this->has('delivery_type')) {
            $this->merge(['delivery_type' => strtolower($this->input('delivery_type'))]);
        }

        if ($this->has('payment_method')) {
            $this->merge(['payment_method' => strtolower($this->input('payment_method'))]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $statusValues = implode(',', OrderStatus::values());
        $deliveryTypeValues = implode(',', DeliveryType::values());
        $paymentMethodValues = implode(',', PaymentMethod::values());

        return [
            'order_number' => 'sometimes|string|max:255',
            'status' => "sometimes|string|in:{$statusValues}",
            'delivery_type' => "sometimes|string|in:{$deliveryTypeValues}",
            'payment_method' => "sometimes|string|in:{$paymentMethodValues}",
            'per_page' => 'sometimes|integer|min:1|max:100',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    public function messages(): array
    {
        $locale = app()->getLocale();
        $statusValues = implode(', ', OrderStatus::values());
        $deliveryTypeValues = implode(' ou ', DeliveryType::values());
        $paymentMethodValues = implode(', ', PaymentMethod::values());

        if ($locale === 'fr') {
            return [
                'status.in' => "Le statut doit être l'une des valeurs suivantes : {$statusValues}",
                'delivery_type.in' => "Le type de livraison doit être : {$deliveryTypeValues}",
                'payment_method.in' => "La méthode de paiement doit être l'une des valeurs suivantes : {$paymentMethodValues}",
                'per_page.min' => 'Le nombre d\'éléments par page doit être au minimum 1',
                'per_page.max' => 'Le nombre d\'éléments par page ne peut pas dépasser 100',
            ];
        }

        // English messages
        $deliveryTypeValuesEn = implode(' or ', DeliveryType::values());

        return [
            'status.in' => "The status must be one of the following values: {$statusValues}",
            'delivery_type.in' => "The delivery type must be: {$deliveryTypeValuesEn}",
            'payment_method.in' => "The payment method must be one of the following values: {$paymentMethodValues}",
            'per_page.min' => 'The number of elements per page must be at least 1',
            'per_page.max' => 'The number of elements per page cannot exceed 100',
        ];
    }
}
