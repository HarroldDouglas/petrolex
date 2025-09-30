<?php

namespace App\Http\Api\Controllers\Customer;

use App\DTOs\Customer\CustomerDeliveryAddressDTO;
use App\Http\Api\Responses\Customer\UpdateCustomerDeliveryAddressResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\UpdateCustomerDeliveryAddressRequest;
use App\Models\Customer;
use App\Models\CustomerDeliveryAddress;
use App\Services\Customer\CustomerService;

class UpdateCustomerDeliveryAddressController extends Controller
{
    public function __construct(
        protected CustomerService $customerService
    ) {}

    /**
     * Update a customer delivery address.
     *
     * Route: PUT /my/delivery-addresses/{deliveryAddress}
     * Name: api.my.delivery-addresses.update
     */
    public function __invoke(
        UpdateCustomerDeliveryAddressRequest $request,
        CustomerDeliveryAddress $deliveryAddress
    ): UpdateCustomerDeliveryAddressResponse {
        $customer = $request->user()->customer;

        // Ensure the delivery address belongs to the authenticated customer
        if ($deliveryAddress->customer_id !== $customer->id) {
            abort(404, 'Delivery address not found for this customer');
        }

        $dto = CustomerDeliveryAddressDTO::from($request->validated());
        $updatedAddress = $this->customerService->updateDeliveryAddress($deliveryAddress, $dto);

        return UpdateCustomerDeliveryAddressResponse::withAddress($updatedAddress);
    }
}
