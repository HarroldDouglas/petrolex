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

    /**
     * Add customer feedback to the specified order.
     *
     * Route: POST /{order}/customer-feedback
     * Name: orders.customer-feedback
     * Documentation: See documentation/Order/AddCustomerCommentToOrderDoc.php
     */
    public function __invoke(AddCustomerCommentToOrderRequest $request, Order $order): AddCustomerCommentToOrderResponse
    {
        // Security check: Ensure the order belongs to the authenticated customer
        $authenticatedUser = $request->user();
        if (! $authenticatedUser->customer || $order->customer_id !== $authenticatedUser->customer->id) {
            abort(403, 'Cette commande ne vous appartient pas.');
        }

        $dto = new AddCustomerCommentToOrderDTO(
            comments: $request->input('comments'),
            rating: (float) $request->input('rating'),
        );

        $order = $this->orderService->update($order, $dto->toArrayFiltered());

        return AddCustomerCommentToOrderResponse::withOrder($order);
    }
}
