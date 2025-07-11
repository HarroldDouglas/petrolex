<?php

namespace App\Http\Requests\Order;

class StoreOrderRequest extends AbstractOrderRequest
{
    public function authorize(): bool
    {
        return true;
    }
}
