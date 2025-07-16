<?php

namespace App\Http\Api\Controllers\Order;

use App\DTOs\Order\CreateOrderDTO;
use App\Http\Api\Responses\Order\StoreOrderResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Services\Order\OrderService;

class StoreOrderController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {}

    /**
     * Handle the incoming request.
     */
    public function __invoke(StoreOrderRequest $request): StoreOrderResponse
    {
        $orderDTO = CreateOrderDTO::from($request->validated());
        $order = $this->orderService->createOrder($orderDTO);

        return StoreOrderResponse::withOrder($order);
    }
}
