<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class RealTimeTrackingController extends Controller
{
    /**
     * Display the real-time tracking page for an order
     */
    public function __invoke(Request $request, $orderId)
    {
        // Get the order by ID
        $order = Order::find($orderId);

        if (! $order) {
            abort(404, 'Order not found');
        }

        // Load necessary relations
        $order->load([
            'customer',
            'deliveryAddress',
            'deliveryPerson.user',
            'distributionCenter',
            'deliveryTracking',
        ]);

        // Data for the view
        $trackingConfig = [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'google_maps' => [
                'api_key' => config('services.google_maps.api_key', env('GOOGLE_MAPS_API_KEY', 'AIzaSyB0w8HLsobdoJgK7WUTQxLFUuZOirvmUCI')),
            ],
            'websocket' => [
                'enabled' => true,
                'key' => config('broadcasting.connections.reverb.key', 'local-key'),
                'cluster' => config('broadcasting.connections.reverb.options.cluster', 'mt1'),
                'host' => config('broadcasting.connections.reverb.options.host', '127.0.0.1'),
                'port' => request()->secure() ? 443 : config('broadcasting.connections.reverb.port', 8080),
                'force_tls' => request()->secure(),
            ],
            'api_endpoints' => [
                'tracking_details' => route('tracking.delivery.details', $order->id),
                'tracking_updates' => route('tracking.delivery.position.update', $order->id),
            ],
        ];

        return view('orders.real-time-tracking', compact('order', 'trackingConfig'));
    }
}
