<?php

declare(strict_types=1);

namespace App\Http\Api\Requests\Order;

use App\Enums\DeliveryType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GetFilteredOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => $this->input('status') ? strtolower($this->input('status')) : null,
            'delivery_type' => $this->input('delivery_type') ? strtolower($this->input('delivery_type')) : null,
            'payment_method' => $this->input('payment_method') ? strtolower($this->input('payment_method')) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::in(OrderStatus::values())],
            'order_number' => ['nullable', 'string'],
            'delivery_type' => ['nullable', Rule::in(DeliveryType::values())],
            'payment_method' => ['nullable', Rule::in(PaymentMethod::values())],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'Le statut de commande sélectionné est invalide. Valeurs acceptées : '.implode(', ', OrderStatus::values()).'.',
            'delivery_type.in' => 'Le type de livraison sélectionné est invalide. Valeurs acceptées : '.implode(', ', DeliveryType::values()).'.',
            'payment_method.in' => 'La méthode de paiement sélectionnée est invalide. Valeurs acceptées : '.implode(', ', PaymentMethod::values()).'.',
            'per_page.integer' => 'Le nombre d\'éléments par page doit être un entier.',
            'per_page.min' => 'Le nombre d\'éléments par page doit être au moins 1.',
            'per_page.max' => 'Le nombre d\'éléments par page ne peut pas dépasser 100.',
        ];
    }
}
