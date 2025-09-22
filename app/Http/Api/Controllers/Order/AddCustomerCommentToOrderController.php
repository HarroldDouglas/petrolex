<?php

declare(strict_types=1);

namespace App\Http\Api\Controllers\Order;

use App\DTOs\Order\AddCustomerCommentToOrderDTO;
use App\Http\Api\Requests\Order\AddCustomerCommentToOrderRequest;
use App\Http\Api\Responses\Order\OrderDetailsResponse;
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
    public function __invoke(AddCustomerCommentToOrderRequest $request, Order $order): OrderDetailsResponse
    {
        // Security check: Ensure the order belongs to the authenticated customer
        $authenticatedUser = $request->user();
        if (! $authenticatedUser->customer || $order->customer_id !== $authenticatedUser->customer->id) {
            abort(403, __('api.order_not_belongs_to_you'));
        }

        // Business rule: Only delivered or cancelled orders can receive feedback
        $allowedStatuses = [\App\Enums\OrderStatus::DELIVERED()->value, \App\Enums\OrderStatus::CANCELLED()->value];
        if (! in_array($order->status, $allowedStatuses)) {
            abort(422, __('api.order_cannot_receive_feedback'));
        }

        $dto = new AddCustomerCommentToOrderDTO(
            comments: $request->input('comments'),
            rating: (float) $request->input('rating'),
        );

        /** @var Order $order */
        $order = $this->orderService->update($order, $dto->toArrayFiltered());

        $order->loadDetailRelations();

        return OrderDetailsResponse::withOrder($order, __('api.order_comment_added_success'));
    }
}
