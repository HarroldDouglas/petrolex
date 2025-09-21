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
        // Security check: Only order owner (customer) and delivery persons can view order details
        $authenticatedUser = $request->user();
        $isOrderOwner = $authenticatedUser->customer && $authenticatedUser->customer->id === $order->customer_id;
        $isDeliveryPerson = $authenticatedUser->hasRole('delivery_person');

        if (! $isOrderOwner && ! $isDeliveryPerson) {
            abort(403, 'Cette commande ne vous appartient pas.');
        }

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
