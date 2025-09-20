<?php

return [
    'messages' => [
        'delivery_address_id.required' => 'The delivery address is required',
        'delivery_address_id.exists' => 'The selected delivery address does not exist',
        'distribution_center_id.required' => 'The distribution center is required',
        'distribution_center_id.exists' => 'The selected distribution center does not exist',
        'delivery_type.in' => 'The delivery type must be one of: :values',
        'payment_method.in' => 'The payment method must be one of: :values',
        'items.required' => 'At least one item is required',
        'items.min' => 'At least one item is required',
        'items.max' => 'You cannot order more than 50 different items',
        'items.*.product_category_id.required' => 'The product category ID is required',
        'items.*.product_category_id.exists' => 'The selected product category does not exist',
        'items.*.quantity.required' => 'The quantity is required',
        'items.*.quantity.min' => 'The quantity must be at least 1',
        'items.*.quantity.max' => 'The quantity cannot exceed 100',
        'items.*.option.in' => 'The option must be one of the following values: :values',
        'comments.max' => 'Comments cannot exceed 500 characters',
    ],

    'attributes' => [
        'delivery_address_id' => 'delivery address',
        'distribution_center_id' => 'distribution center',
        'delivery_type' => 'delivery type',
        'payment_method' => 'payment method',
        'items' => 'items',
        'items.*.product_category_id' => 'product category',
        'items.*.quantity' => 'quantity',
        'items.*.option' => 'option',
        'comments' => 'comments',
    ],
];
