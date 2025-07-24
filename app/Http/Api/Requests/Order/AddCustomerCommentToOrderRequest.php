<?php

declare(strict_types=1);

namespace App\Http\Api\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class AddCustomerCommentToOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'comment' => ['required', 'string', 'min:10'],
            'rating' => ['required', 'numeric', 'min:1', 'max:5'],
        ];
    }
}
