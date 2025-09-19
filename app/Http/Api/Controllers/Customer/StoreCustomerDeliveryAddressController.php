<?php

namespace App\Http\Api\Controllers\Customer;

use App\DTOs\Customer\CustomerDeliveryAddressDTO;
use App\Http\Api\Responses\Customer\StoreCustomerDeliveryAddressResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreCustomerDeliveryAddressRequest;
use App\Models\Customer;
use App\Services\Customer\CustomerService;

class StoreCustomerDeliveryAddressController extends Controller
{
    public function __construct(
        protected CustomerService $customerService
    ) {}

    /**
     * Store new customer delivery address.
     *
     * Route: POST /customers/{customer}/delivery-addresses
     * Name: api.customers.delivery-addresses.store
     */
    public function __invoke(StoreCustomerDeliveryAddressRequest $request, Customer $customer): StoreCustomerDeliveryAddressResponse
    {
        $dto = CustomerDeliveryAddressDTO::from($request->validated());
        $deliveryAddress = $this->customerService->createDeliveryAddress($customer, $dto);

        return StoreCustomerDeliveryAddressResponse::withAddress($deliveryAddress);
    }
}
