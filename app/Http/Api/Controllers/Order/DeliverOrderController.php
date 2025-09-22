<?php

declare(strict_types=1);

namespace App\Http\Api\Controllers\Order;

use App\Http\Api\Responses\Order\OrderDetailsResponse;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Order\OrderService;

final class DeliverOrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService
    ) {}

    /**
     * Mark an order as delivered.
     *
     * Route: PATCH /api/orders/{order}/deliver
     * Name: api.orders.deliver
     */
    public function __invoke(Order $order): OrderDetailsResponse
    {
        $user = auth()->user();

        $isAssignedDeliveryPerson = $user->deliveryPerson && $order->delivery_person_id === $user->deliveryPerson->id;
        $isOrderCustomer = $user->customer && $order->customer_id === $user->customer->id;

        if (! $isAssignedDeliveryPerson && ! $isOrderCustomer) {
            abort(403, __('api.order_not_authorized_to_deliver'));
        }

        $updatedOrder = $this->orderService->deliverOrder($order);

        if (empty($updatedOrder)) {
            abort(422, __('api.order_cannot_be_delivered'));
        }

        $updatedOrder->loadDetailRelations();

        return OrderDetailsResponse::withOrder($updatedOrder, __('api.order_delivered_success'));
    }
}
