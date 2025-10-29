<?php

declare(strict_types=1);

namespace App\Http\Api\Controllers;

use App\DTOs\Order\GetOrdersFilterDTO;
use App\Http\Api\Requests\Order\GetFilteredOrderRequest;
use App\Http\Api\Responses\Customer\CustomerOrdersResponse;
use App\Http\Controllers\Controller;
use App\Services\Customer\CustomerService;
use App\Services\DeliveryPersonService;
use Illuminate\Support\Arr;

final class GetMyOrdersController extends Controller
{
    public function __construct(
        private readonly CustomerService $customerService,
        private readonly DeliveryPersonService $deliveryPersonService
    ) {}

    /**
     * Get user orders (dispatches to appropriate service based on user role).
     *
     * Route: GET /api/my/orders
     * Name: api.my.orders.index
     */
    public function __invoke(GetFilteredOrderRequest $request): CustomerOrdersResponse
    {
        $user = $request->user();
        $validated = $request->validated();
        $filters = GetOrdersFilterDTO::from(Arr::except($validated, ['per_page']));
        $perPage = (int) ($validated['per_page'] ?? 10);

        // Delegate to appropriate service based on user role
        if ($user->deliveryPerson) {
            $orders = $this->deliveryPersonService->getOrders($user->deliveryPerson, $filters, $perPage);
            $message = __('api.delivery_person_orders_retrieved_success');
        } elseif ($user->customer) {
            $orders = $this->customerService->getOrders($user->customer, $filters, $perPage);
            $message = __('api.customer_orders_retrieved_success');
        } else {
            abort(403, __('api.order_not_authorized_role_required'));
        }

        return CustomerOrdersResponse::paginatedCollection($orders, $message);
    }
}
