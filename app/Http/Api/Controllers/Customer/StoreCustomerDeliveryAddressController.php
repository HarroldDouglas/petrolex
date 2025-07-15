<?php

namespace App\Http\Api\Controllers\Customer;

use App\DTOs\Customer\CustomerDeliveryAddressDTO;
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
        $dto = CustomerDeliveryAddressDTO::from($request->validated());
        $deliveryAddress = $customer->deliveryAddresses()->create($dto->toArray());

        return StoreCustomerDeliveryAddressResponse::withAddress($deliveryAddress);
    }
}
