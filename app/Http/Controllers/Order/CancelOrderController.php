<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Order\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CancelOrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService
    ) {}

   
    public function __invoke(Request $request, Order $order): RedirectResponse
    {
        try {
            $this->orderService->cancelOrder($order->id);

            return redirect()->route('orders.details', $order->id)
                ->with('success', 'Commande annulée avec succès');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }
}
