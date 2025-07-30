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

    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::in(OrderStatus::values())],
            'order_number' => ['nullable', 'string'],
            'delivery_type' => ['nullable', Rule::in(DeliveryType::values())],
            'payment_method' => ['nullable', Rule::in(PaymentMethod::values())],
            'per_page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'Le statut de commande sélectionné est invalide.',
            'delivery_type.in' => 'Le type de livraison sélectionné est invalide.',
            'payment_method.in' => 'La méthode de paiement sélectionnée est invalide.',
            'per_page.integer' => 'Le nombre d\'éléments par page doit être un entier.',
            'per_page.min' => 'Le nombre d\'éléments par page doit être au moins 1.',
        ];
    }
}
