<?php

namespace App\Http\Api\Controllers\Customer;

use App\Http\Api\Responses\Customer\CustomerResponse;
use App\Http\Controllers\Controller;
use App\Services\Customer\CustomerService;

class GetCustomerController extends Controller
{
    public function __construct(protected CustomerService $customerService) {}

    /**
     * Get a single customer by ID.
     *
     * @param int $customerId
     */
    public function __invoke(int $customerId): CustomerResponse
    {
        $customer = $this->customerService->find($customerId);

        return CustomerResponse::single($customer);
    }
}
