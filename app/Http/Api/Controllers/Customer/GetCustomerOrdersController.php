<?php

namespace App\Http\Api\Controllers\Customer;

use App\DTOs\Order\GetOrdersFilterDTO;
use App\Http\Api\Requests\Customer\GetCustomerOrdersRequest;
use App\Http\Api\Responses\Customer\CustomerOrdersResponse;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\Customer\CustomerService;

use Illuminate\Support\Arr;

class GetCustomerOrdersController extends Controller
{
    public function __construct(
        protected CustomerService $customerService
    ) {}

    public function __invoke(GetCustomerOrdersRequest $request, Customer $customer): CustomerOrdersResponse
    {
        $validated = $request->validated();
        $filters = GetOrdersFilterDTO::from(Arr::except($validated, ['per_page']));
        $perPage = $validated['per_page'] ?? 10;

        $orders = $this->customerService->getOrders($customer, $filters, $perPage);

        return CustomerOrdersResponse::paginatedCollection($orders);
    }
}
