<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class RealTimeTrackingController extends Controller
{
    /**
     * Afficher la page de suivi en temps réel pour une commande
     */
    public function __invoke(Request $request, $orderId)
    {
        // Récupérer la commande par ID
        $order = Order::find($orderId);

        if (! $order) {
            abort(404, 'Commande non trouvée');
        }

        // Charger les relations nécessaires
        $order->load([
            'customer',
            'deliveryAddress',
            'deliveryPerson.user',
            'distributionCenter',
            'deliveryTracking',
        ]);

        // Données pour la vue
        $trackingConfig = [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'mapbox' => [
                'access_token' => config('services.mapbox.token'),
            ],
            'websocket' => [
                'enabled' => true, // Toujours activé puisque Reverb fonctionne
                'key' => config('broadcasting.connections.reverb.key', 'local-key'),
                'cluster' => config('broadcasting.connections.reverb.options.cluster', 'mt1'),
                'host' => config('broadcasting.connections.reverb.options.host', '127.0.0.1'),
                'port' => config('broadcasting.connections.reverb.port', 8080), // Port Reverb correct
                'force_tls' => false,
            ],
            'api_endpoints' => [
                'tracking_details' => route('tracking.delivery.details', $order->id),
                'tracking_updates' => route('tracking.delivery.position.update', $order->id),
            ],
        ];

        return view('orders.real-time-tracking', compact('order', 'trackingConfig'));
    }
}
