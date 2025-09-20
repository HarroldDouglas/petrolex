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
        $deliveryTypeValues = implode(', ', DeliveryType::values());
        $paymentMethodValues = implode(', ', PaymentMethod::values());
        $bottleOrderTypeValues = implode(', ', BottleOrderType::values());

        return [
            'delivery_type.in' => "The delivery type must be one of: {$deliveryTypeValues}",
            'payment_method.in' => "The payment method must be one of: {$paymentMethodValues}",
            'items.*.option.in' => "The option must be one of: {$bottleOrderTypeValues}",
        ];
    }

    public function attributes(): array
    {
        $attributes = trans('validation.order.attributes');

        return is_array($attributes) ? $attributes : [];
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
