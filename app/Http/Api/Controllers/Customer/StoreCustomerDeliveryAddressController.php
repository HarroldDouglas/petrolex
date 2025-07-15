<?php

namespace App\Http\Api\Controllers\Customer;

use App\Http\Api\Responses\Customer\StoreCustomerDeliveryAddressResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreCustomerDeliveryAddressRequest;
use App\Models\Customer;

class StoreCustomerDeliveryAddressController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(StoreCustomerDeliveryAddressRequest $request, Customer $customer): StoreCustomerDeliveryAddressResponse
    {
        $deliveryAddress = $customer->deliveryAddresses()->create($request->validated());

        return StoreCustomerDeliveryAddressResponse::withAddress($deliveryAddress);
    }
}
