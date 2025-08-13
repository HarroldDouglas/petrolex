<?php

declare(strict_types=1);

namespace App\Http\Api\Controllers\Order;

use App\Http\Api\Responses\Order\OrderDeliveredResponse;
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
    public function __invoke(Order $order): OrderDeliveredResponse
    {
        $this->orderService->deliverOrder($order);

        return OrderDeliveredResponse::delivered($order);
    }
}
