<?php

namespace App\Http\Api\Controllers\Customer;

use App\DTOs\Order\GetOrdersFilterDTO;
use App\Http\Api\Responses\Customer\CustomerOrdersResponse;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\Customer\CustomerService;
use Illuminate\Http\Request;

class GetCustomerOrdersController extends Controller
{
    public function __construct(
        protected CustomerService $customerService
    ) {}

    public function __invoke(Request $request, Customer $customer): CustomerOrdersResponse
    {
        $filters = GetOrdersFilterDTO::from($request->only(['order_number', 'status', 'delivery_type', 'payment_method']));
        $perPage = $request->input('per_page', 10);

        $orders = $this->customerService->getOrders($customer, $filters, $perPage);

        return CustomerOrdersResponse::paginatedCollection($orders);
    }
}
