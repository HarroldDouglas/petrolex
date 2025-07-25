<?php

declare(strict_types=1);

namespace App\Http\Api\Controllers\Order;

use App\DTOs\Order\CancelOrderDTO;
use App\Enums\OrderStatus;
use App\Http\Api\Responses\Order\CancelOrderResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\CancelOrderRequest;
use App\Models\Order;
use App\Services\Order\OrderService;

class CancelOrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    public function __invoke(CancelOrderRequest $request, Order $order): CancelOrderResponse
    {
        $dto = new CancelOrderDTO(
            cancelled_reason: $request->input('cancelled_reason'),
            cancelled_by: (int) $request->input('cancelled_by'),
        );

        $dtoArray = $dto->toArray();
        $dtoArray['status'] = OrderStatus::CANCELLED();
        $dtoArray['cancelled_at'] = now();
        $order = $this->orderService->update($order, $dtoArray);

        return CancelOrderResponse::withOrder($order);
    }
}
