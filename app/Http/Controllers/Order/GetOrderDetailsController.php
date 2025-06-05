<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Services\Order\OrderService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GetOrderDetailsController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {}

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, int $orderId): View
    {
        $orderDetails = $this->orderService->getOrderWithGroupedItems($orderId);

        if (! $orderDetails) {
            abort(Response::HTTP_NOT_FOUND, 'Commande introuvable');
        }

        return view('orders.order-details', [
            'order' => $orderDetails->order,
            'groupedItems' => $orderDetails->groupedItems,
        ]);
    }
}
