<?php

declare(strict_types=1);

namespace App\Http\Api\Controllers\Order;

use App\DTOs\Order\CreateOrderWithoutPaymentDTO;
use App\Http\Api\Requests\Order\CreateOrderRequest;
use App\Http\Api\Responses\Order\CreateOrderResponse;
use App\Http\Controllers\Controller;
use App\Services\Order\OrderService;

final class CreateOrderController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {}

    /**
     * Create a new order.
     *
     * Route: POST /api/orders
     * Name: api.orders.store
     */
    public function __invoke(CreateOrderRequest $request): CreateOrderResponse
    {
        $data = $request->validated();
        $data['customer_id'] = $request->user()->customer->id;

        $orderDTO = CreateOrderWithoutPaymentDTO::from($data);

        try {
            $order = $this->orderService->createWithoutPayment($orderDTO);
        } catch (\InvalidArgumentException $e) {
            // Convert InvalidArgumentException to validation error (422)
            throw \Illuminate\Validation\ValidationException::withMessages([
                'distribution_center_id' => [$e->getMessage()],
            ]);
        }

        $order->loadDetailRelations();

        return CreateOrderResponse::withOrder($order);
    }
}
