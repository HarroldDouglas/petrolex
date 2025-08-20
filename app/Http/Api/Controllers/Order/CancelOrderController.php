<?php

declare(strict_types=1);

namespace App\Http\Api\Controllers\Order;

use App\Enums\OrderStatus;
use App\Http\Api\Responses\Order\CancelOrderResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\CancelOrderRequest;
use App\Models\Order;
use App\Services\Order\OrderService;

class CancelOrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    /**
     * Cancel the specified order.
     *
     * Route: PATCH /orders/{order}/cancel
     * Name: orders.cancel
     */
    public function __invoke(CancelOrderRequest $request, Order $order): CancelOrderResponse
    {
        $data = [
            'cancelled_reason' => $request->input('cancelled_reason'),
            'cancelled_by' => (int) $request->input('cancelled_by'),
            'status' => OrderStatus::CANCELLED(),
            'cancelled_at' => now(),
        ];

        $order = $this->orderService->update($order, $data);

        return CancelOrderResponse::withOrder($order);
    }
}
