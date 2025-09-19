<?php

namespace App\Http\Requests\Customer;

class UpdateCustomerDeliveryAddressRequest extends AbstractCustomerDeliveryAddressRequest
{
    public function authorize(): bool
    {
        return true;
    }
}
