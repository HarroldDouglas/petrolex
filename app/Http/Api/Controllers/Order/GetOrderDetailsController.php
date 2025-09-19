<?php

namespace App\Http\Api\Controllers\Order;

use App\Http\Api\Responses\Order\OrderDetailsResponse;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class GetOrderDetailsController extends Controller
{
    /**
     * Get order details.
     *
     * Route: GET /api/orders/{order}
     * Name: api.orders.show
     */
    public function __invoke(Request $request, Order $order): OrderDetailsResponse
    {
        $order->load([
            'customer.user.country',
            'customer.deliveryAddresses.neighborhood.municipality.city.country',
            'deliveryAddress.neighborhood.municipality.city.country',
            'deliveryPerson.user',
            'distributionCenter',
            'payment',
            'items.productCategory',
            'refunds',
            'deliveryTracking',
        ]);

        return OrderDetailsResponse::withOrder($order);
    }
}
