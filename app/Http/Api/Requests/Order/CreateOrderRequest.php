<?php

declare(strict_types=1);

namespace App\Http\Api\Requests\Order;

use App\Enums\BottleOrderType;
use App\Enums\DeliveryType;
use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;

final class CreateOrderRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $deliveryTypeValues = implode(',', DeliveryType::values());
        $paymentMethodValues = implode(',', PaymentMethod::values());
        $bottleOrderTypeValues = implode(',', BottleOrderType::values());

        return [
            'delivery_address_id' => [
                'required',
                'integer',
                'exists:customer_delivery_addresses,id',
                function ($attribute, $value, $fail) {
                    // Verify that the delivery address belongs to the authenticated customer
                    $customer = $this->user()->customer;
                    if (! $customer->deliveryAddresses()->where('id', $value)->exists()) {
                        $fail(__('validation.custom.delivery_address_not_owned'));
                    }
                },
            ],
            'distribution_center_id' => [
                'required',
                'integer',
                'exists:distribution_centers,id',
            ],
            'delivery_type' => "required|string|in:{$deliveryTypeValues}",
            'payment_method' => "required|string|in:{$paymentMethodValues}",
            'items' => 'required|array|min:1|max:50',
            'items.*.product_category_id' => [
                'required',
                'integer',
                'exists:product_categories,id',
            ],
            'items.*.quantity' => 'required|integer|min:1|max:100',
            'items.*.option' => "nullable|string|in:{$bottleOrderTypeValues}",
            'comments' => 'nullable|string|max:500',
        ];
    }

    public function authorize(): bool
    {
        return $this->user() && $this->user()->customer;
    }

    public function messages(): array
    {
        $locale = app()->getLocale();
        $deliveryTypeValues = implode(' ou ', DeliveryType::values());
        $paymentMethodValues = implode(', ', PaymentMethod::values());
        $bottleOrderTypeValues = implode(', ', BottleOrderType::values());

        if ($locale === 'fr') {
            return [
                'delivery_address_id.required' => 'L\'adresse de livraison est obligatoire',
                'delivery_address_id.exists' => 'L\'adresse de livraison sélectionnée n\'existe pas',
                'distribution_center_id.required' => 'Le centre de distribution est obligatoire',
                'distribution_center_id.exists' => 'Le centre de distribution sélectionné n\'existe pas',
                'delivery_type.in' => "Le type de livraison doit être : {$deliveryTypeValues}",
                'payment_method.in' => "La méthode de paiement doit être l'une des valeurs suivantes : {$paymentMethodValues}",
                'items.required' => 'Au moins un article est requis',
                'items.min' => 'Au moins un article est requis',
                'items.max' => 'Vous ne pouvez pas commander plus de 50 articles différents',
                'items.*.product_category_id.required' => 'L\'ID de la catégorie de produit est obligatoire',
                'items.*.product_category_id.exists' => 'La catégorie de produit sélectionnée n\'existe pas',
                'items.*.quantity.required' => 'La quantité est obligatoire',
                'items.*.quantity.min' => 'La quantité doit être au minimum de 1',
                'items.*.quantity.max' => 'La quantité ne peut pas dépasser 100',
                'items.*.option.in' => "L'option doit être l'une des valeurs suivantes : {$bottleOrderTypeValues}",
                'comments.max' => 'Les commentaires ne peuvent pas dépasser 500 caractères',
            ];
        }

        // English messages
        return [
            'delivery_address_id.required' => 'The delivery address is required',
            'delivery_address_id.exists' => 'The selected delivery address does not exist',
            'distribution_center_id.required' => 'The distribution center is required',
            'distribution_center_id.exists' => 'The selected distribution center does not exist',
            'delivery_type.in' => "The delivery type must be: {$deliveryTypeValues}",
            'payment_method.in' => "The payment method must be one of the following values: {$paymentMethodValues}",
            'items.required' => 'At least one item is required',
            'items.min' => 'At least one item is required',
            'items.max' => 'You cannot order more than 50 different items',
            'items.*.product_category_id.required' => 'The product category ID is required',
            'items.*.product_category_id.exists' => 'The selected product category does not exist',
            'items.*.quantity.required' => 'The quantity is required',
            'items.*.quantity.min' => 'The quantity must be at least 1',
            'items.*.quantity.max' => 'The quantity cannot exceed 100',
            'items.*.option.in' => "The option must be one of the following values: {$bottleOrderTypeValues}",
            'comments.max' => 'Comments cannot exceed 500 characters',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        $locale = app()->getLocale();

        if ($locale === 'fr') {
            return [
                'delivery_address_id' => 'adresse de livraison',
                'distribution_center_id' => 'centre de distribution',
                'delivery_type' => 'type de livraison',
                'payment_method' => 'méthode de paiement',
                'items' => 'articles',
                'items.*.product_category_id' => 'catégorie de produit',
                'items.*.quantity' => 'quantité',
                'items.*.option' => 'option',
                'comments' => 'commentaires',
            ];
        }

        return [
            'delivery_address_id' => 'delivery address',
            'distribution_center_id' => 'distribution center',
            'delivery_type' => 'delivery type',
            'payment_method' => 'payment method',
            'items' => 'items',
            'items.*.product_category_id' => 'product category',
            'items.*.quantity' => 'quantity',
            'items.*.option' => 'option',
            'comments' => 'comments',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Add customer_id to the request data for DTO creation if user has customer relation
        if ($this->user() && $this->user()->customer) {
            $this->merge([
                'customer_id' => $this->user()->customer->id,
            ]);
        }
    }
}
