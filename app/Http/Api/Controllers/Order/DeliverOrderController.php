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
        $user = auth()->user();

        // Security check: Only the delivery person assigned to this order can mark it as delivered
        $isAssignedDeliveryPerson = $user->deliveryPerson && $order->delivery_person_id === $user->deliveryPerson->id;

        if (! $isAssignedDeliveryPerson) {
            abort(403, 'Vous n\'êtes pas autorisé à marquer cette commande comme livrée.');
        }

        $updatedOrder = $this->orderService->deliverOrder($order);
        $message = empty($updatedOrder) ? 'This order cannot be marked as delivered.'
           : 'Order marked as delivered successfully.';

        return OrderDeliveredResponse::delivered($order, $message, ! empty($updatedOrder));
    }
}
