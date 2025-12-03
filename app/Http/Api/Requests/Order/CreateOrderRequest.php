<?php

declare(strict_types=1);

namespace App\Http\Api\Requests\Order;

use App\Enums\BottleOrderType;
use App\Enums\DeliveryType;
use Illuminate\Foundation\Http\FormRequest;

final class CreateOrderRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $deliveryTypeValues = implode(',', DeliveryType::values());
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
            'items' => 'required|array|min:1|max:50',
            'items.*.product_category_id' => [
                'required',
                'integer',
                'exists:product_categories,id',
            ],
            'items.*.quantity' => 'required|integer|min:1|max:100',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.option' => "nullable|string|in:{$bottleOrderTypeValues}",
            'delivery_fee' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
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
        $bottleOrderTypeValues = implode(', ', BottleOrderType::values());

        return [
            'delivery_type.in' => "The delivery type must be one of: {$deliveryTypeValues}",
            'items.*.option.in' => "The option must be one of: {$bottleOrderTypeValues}",
            'items.*.unit_price.required' => __('validation/order.unit_price_required'),
            'items.*.unit_price.numeric' => __('validation/order.unit_price_numeric'),
            'delivery_fee.required' => __('validation/order.delivery_fee_required'),
            'delivery_fee.numeric' => __('validation/order.delivery_fee_numeric'),
            'total_amount.required' => __('validation/order.total_amount_required'),
            'total_amount.numeric' => __('validation/order.total_amount_numeric'),
        ];
    }

    public function attributes(): array
    {
        $attributes = trans('validation/order.attributes');

        return is_array($attributes) ? $attributes : [];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $this->validatePricing($validator);
            // $this->validateStock($validator); // Temporarily disabled for testing
            $this->validateDeliveryFee($validator);
            $this->validateTotalAmount($validator);
        });
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('delivery_type')) {
            $this->merge(['delivery_type' => strtolower($this->input('delivery_type'))]);
        }

        // Convert item options to lowercase
        if ($this->has('items') && is_array($this->input('items'))) {
            $items = $this->input('items');
            foreach ($items as $index => $item) {
                if (isset($item['option'])) {
                    $items[$index]['option'] = strtolower($item['option']);
                }
            }
            $this->merge(['items' => $items]);
        }

        // Add customer_id to the request data for DTO creation if user has customer relation
        if ($this->user() && $this->user()->customer) {
            $this->merge([
                'customer_id' => $this->user()->customer->id,
            ]);
        }
    }

    private function validatePricing($validator): void
    {
        foreach ($this->input('items', []) as $index => $item) {
            // Skip validation if required fields are missing (will be caught by basic validation)
            if (! isset($item['product_category_id']) || ! isset($item['unit_price'])) {
                continue;
            }

            $expectedPrice = app(\App\Services\ProductCategoryService::class)->getProductPrice(
                $item['product_category_id'],
                isset($item['option']) ? BottleOrderType::from($item['option']) : null
            );

            if (abs($item['unit_price'] - $expectedPrice) > 0.01) {
                $validator->errors()->add("items.{$index}.unit_price",
                    __('validation/order.price_mismatch', [
                        'expected' => $expectedPrice,
                        'provided' => $item['unit_price'],
                    ])
                );
            }
        }
    }

    private function validateDeliveryFee($validator): void
    {
        $deliveryTypeValue = $this->input('delivery_type');
        $providedFee = $this->input('delivery_fee');

        // Skip validation if delivery_type or delivery_fee is missing (will be caught by basic validation)
        if (! $deliveryTypeValue || ! is_numeric($providedFee)) {
            return;
        }

        $deliveryType = DeliveryType::from($deliveryTypeValue);
        $expectedFee = $deliveryType->fee();

        if (abs($providedFee - $expectedFee) > 0.01) {
            $validator->errors()->add('delivery_fee',
                __('validation/order.delivery_fee_mismatch', [
                    'expected' => $expectedFee,
                    'provided' => $providedFee,
                ])
            );
        }
    }

    private function validateTotalAmount($validator): void
    {
        $items = $this->input('items', []);
        $deliveryFee = $this->input('delivery_fee');
        $providedTotal = $this->input('total_amount');

        if (! is_array($items) || ! is_numeric($deliveryFee) || ! is_numeric($providedTotal)) {
            return;
        }

        $itemsTotal = collect($items)->sum(function ($item) {
            // Skip if item doesn't have required fields
            if (! isset($item['unit_price']) || ! isset($item['quantity'])) {
                return 0;
            }

            return $item['unit_price'] * $item['quantity'];
        });

        $expectedTotal = $itemsTotal + $deliveryFee;

        if (abs($providedTotal - $expectedTotal) > 0.01) {
            $validator->errors()->add('total_amount',
                __('validation/order.total_amount_mismatch', [
                    'expected' => $expectedTotal,
                    'provided' => $providedTotal,
                ])
            );
        }
    }
}
