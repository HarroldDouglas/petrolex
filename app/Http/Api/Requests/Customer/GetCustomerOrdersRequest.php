<?php

declare(strict_types=1);

namespace App\Http\Api\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

final class GetCustomerOrdersRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'order_number' => 'sometimes|string|max:255',
            'status' => 'sometimes|string|max:255',
            'delivery_type' => 'sometimes|string|max:255',
            'payment_method' => 'sometimes|string|max:255',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
