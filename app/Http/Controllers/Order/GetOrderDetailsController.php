<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class GetOrderDetailsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Order $order)
    {
        // let's load the necessary relationships for the order
        $order->load([
            'customer',
            'deliveryAddress',
            'distributionCenter',
            'deliveryPerson',
            'items.product',
            'items.product.bottle',
            'items.product.accessory',
        ]);

        return view('orders.order-details', compact('order'));
    }
}
