<?php

namespace App\Http\Api\Controllers\Customer;

use App\Http\Api\Responses\Customer\CustomerResponse;
use App\Http\Controllers\Controller;
use App\Services\Customer\CustomerService;
use App\Services\User\UserService;

class GetCustomersController extends Controller
{
    public function __construct(protected CustomerService $customerService) {}

    /**
     * Get all customers.
     *
     * Route: GET /customers
     * Name: api.customers
     */
    public function __invoke(): CustomerResponse
    {
        $customers = $this->customerService->getAll();

        return CustomerResponse::many($customers);
    }
}
