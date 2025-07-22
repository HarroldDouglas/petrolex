<?php

namespace App\Http\Api\Controllers\Order;

use App\Http\Api\Responses\Order\OrdersResponse;
use App\Http\Controllers\Controller;
use App\Services\Order\OrderService;
use Illuminate\Http\JsonResponse;

class GetOrdersController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    public function __invoke(): OrdersResponse
    {
        $orders = $this->orderService->getAll();

        return OrdersResponse::collection($orders);
    }
}
