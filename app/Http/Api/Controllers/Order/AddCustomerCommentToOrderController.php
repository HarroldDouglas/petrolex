<?php

declare(strict_types=1);

namespace App\Http\Api\Controllers\Order;

use App\DTOs\Order\AddCustomerCommentToOrderDTO;
use App\Http\Api\Requests\Order\AddCustomerCommentToOrderRequest;
use App\Http\Api\Responses\Order\AddCustomerCommentToOrderResponse;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Order\OrderService;

class AddCustomerCommentToOrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    public function __invoke(AddCustomerCommentToOrderRequest $request, Order $order): AddCustomerCommentToOrderResponse
    {
        $dto = new AddCustomerCommentToOrderDTO(
            comment: $request->input('comment'),
            rating: (float) $request->input('rating'),
        );

        $order = $this->orderService->update($order, $dto->toArray());

        return AddCustomerCommentToOrderResponse::withOrder($order);
    }
}
