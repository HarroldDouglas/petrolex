<?php

namespace App\Http\Requests\Customer;

class StoreCustomerDeliveryAddressRequest extends AbstractCustomerDeliveryAddressRequest
{
    public function authorize(): bool
    {
        return true;
    }
}
