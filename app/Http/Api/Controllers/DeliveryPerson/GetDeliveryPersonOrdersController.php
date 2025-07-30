<?php

declare(strict_types=1);

namespace App\Http\Api\Controllers\DeliveryPerson;

use App\DTOs\Order\GetOrdersFilterDTO;
use App\Http\Api\Requests\Order\GetFilteredOrderRequest;
use App\Http\Api\Responses\DeliveryPerson\DeliveryPersonOrdersResponse;
use App\Http\Controllers\Controller;
use App\Models\DeliveryPerson;
use App\Services\DeliveryPersonService;

final class GetDeliveryPersonOrdersController extends Controller
{
    public function __construct(private readonly DeliveryPersonService $deliveryPersonService) {}

    /**
     * Get all orders for a specific delivery person.
     *
     * Route: GET /delivery-persons/{deliveryPersonId}/orders
     * Name: api.delivery-persons.orders
     */
    public function __invoke(GetFilteredOrderRequest $request, DeliveryPerson $deliveryPerson): DeliveryPersonOrdersResponse
    {
        $filters = GetOrdersFilterDTO::from($request->only(['status', 'order_number', 'delivery_type']));
        $perPage = $request->input('per_page', 10);

        $orders = $this->deliveryPersonService->getOrders($deliveryPerson, $filters, (int) $perPage);

        return DeliveryPersonOrdersResponse::paginatedCollection($orders);
    }
}
