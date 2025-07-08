<?php

namespace App\Http\Requests\Order;

use App\Enums\BottleOrderType;
use App\Enums\DeliveryType;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class AbstractOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'delivery_address_id' => ['nullable', 'exists:customer_delivery_addresses,id'],
            'distribution_center_id' => ['required', 'exists:distribution_centers,id'],
            'delivery_type' => ['required', Rule::in(DeliveryType::values())],
            'items' => ['required', 'array'],
            'items.*.product_category_id' => ['required', 'exists:product_categories,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.option' => ['required', Rule::in(BottleOrderType::values())],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => 'Le client est requis.',
            'customer_id.exists' => 'Le client sélectionné est invalide.',
            'delivery_address_id.exists' => "L'adresse de livraison sélectionnée est invalide.",
            'distribution_center_id.required' => 'Le centre de distribution est requis.',
            'distribution_center_id.exists' => 'Le centre de distribution sélectionné est invalide.',
            'delivery_type.required' => 'Le type de livraison est requis.',
            'delivery_type.in' => 'Le type de livraison sélectionné est invalide.',
            'items.required' => 'Les articles sont requis.',
            'items.array' => 'Les articles doivent être un tableau.',
            'items.*.product_category_id.required' => 'La catégorie de produit est requise pour chaque article.',
            'items.*.product_category_id.exists' => 'La catégorie de produit sélectionnée pour un article est invalide.',
            'items.*.quantity.required' => 'La quantité est requise pour chaque article.',
            'items.*.quantity.integer' => 'La quantité doit être un entier pour chaque article.',
            'items.*.quantity.min' => 'La quantité doit être au moins 1 pour chaque article.',
            'items.*.option.required' => "L'option est requise pour chaque article.",
            'items.*.option.in' => "L'option sélectionnée pour un article est invalide.",
        ];
    }
}
