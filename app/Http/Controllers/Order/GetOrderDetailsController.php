<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GetOrderDetailsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, $order_id)
    {
        // Données statiques pour le mock-up
        $orders = [
            [
                'id' => 'CMD00120',
                'delivery_address' => 'Centre YDE B',
                'customer_name' => 'Ndongo Carine',
                'items' => [
                    ['name' => 'Bouteille de 9kg', 'quantity' => 3, 'price' => 50.00],
                    ['name' => 'Bouteille de 12kg', 'quantity' => 4, 'price' => 65.00],
                ],
                'total_amount' => 410.00,
                'delivery_man' => 'Eyoum Claire',
                'order_date' => '23/04/2025',
                'status' => 'En cours',
            ],
            [
                'id' => 'CMD00121',
                'delivery_address' => 'Centre YDE C',
                'customer_name' => 'Mbarga Elise',
                'items' => [
                    ['name' => 'Bouteille de 9kg', 'quantity' => 2, 'price' => 50.00],
                    ['name' => 'Bouteille de 12kg', 'quantity' => 5, 'price' => 65.00],
                ],
                'total_amount' => 425.00,
                'delivery_man' => 'Kamga Lionel',
                'order_date' => '24/04/2025',
                'status' => 'Terminée',
            ],
            [
                'id' => 'CMD00122',
                'delivery_address' => 'Centre YDE D',
                'customer_name' => 'Ndongmo Roger',
                'items' => [
                    ['name' => 'Bouteille de 9kg', 'quantity' => 3, 'price' => 50.00],
                    ['name' => 'Bouteille de 12kg', 'quantity' => 4, 'price' => 65.00],
                ],
                'total_amount' => 410.00,
                'delivery_man' => 'Fouda Mireille',
                'order_date' => '24/04/2025',
                'status' => 'Terminée',
            ],
            [
                'id' => 'CMD00123',
                'delivery_address' => 'Centre YDE E',
                'customer_name' => 'Ekani Paul',
                'items' => [
                    ['name' => 'Bouteille de 9kg', 'quantity' => 5, 'price' => 50.00],
                    ['name' => 'Bouteille de 12kg', 'quantity' => 2, 'price' => 65.00],
                ],
                'total_amount' => 380.00,
                'delivery_man' => 'Tchatchoua Paul',
                'order_date' => '25/04/2025',
                'status' => 'Terminée',
            ],
            [
                'id' => 'CMD00124',
                'delivery_address' => 'Centre YDE F',
                'customer_name' => 'Fotso Jules',
                'items' => [
                    ['name' => 'Bouteille de 9kg', 'quantity' => 3, 'price' => 50.00],
                    ['name' => 'Bouteille de 12kg', 'quantity' => 6, 'price' => 65.00],
                ],
                'total_amount' => 540.00,
                'delivery_man' => 'Ngeufack Jean',
                'order_date' => '25/04/2025',
                'status' => 'Terminée',
            ],
            [
                'id' => 'CMD00125',
                'delivery_address' => 'Centre YDE F',
                'customer_name' => 'Fotso Jules',
                'items' => [
                    ['name' => 'Bouteille de 9kg', 'quantity' => 5, 'price' => 50.00],
                    ['name' => 'Bouteille de 12kg', 'quantity' => 3, 'price' => 65.00],
                ],
                'total_amount' => 445.00,
                'delivery_man' => 'Ngeufack Jean',
                'order_date' => '26/04/2025',
                'status' => 'Terminée',
            ],
            [
                'id' => 'CMD00126',
                'delivery_address' => 'Centre YDE F',
                'customer_name' => 'Fotso Jules',
                'items' => [
                    ['name' => 'Bouteille de 9kg', 'quantity' => 3, 'price' => 50.00],
                    ['name' => 'Bouteille de 12kg', 'quantity' => 4, 'price' => 65.00],
                ],
                'total_amount' => 410.00,
                'delivery_man' => 'Ngeufack Jean',
                'order_date' => '27/04/2025',
                'status' => 'Terminée',
            ],
            [
                'id' => 'CMD00127',
                'delivery_address' => 'Centre YDE F',
                'customer_name' => 'Fotso Jules',
                'items' => [
                    ['name' => 'Bouteille de 9kg', 'quantity' => 6, 'price' => 50.00],
                    ['name' => 'Bouteille de 12kg', 'quantity' => 2, 'price' => 65.00],
                ],
                'total_amount' => 430.00,
                'delivery_man' => 'Ngeufack Jean',
                'order_date' => '28/04/2025',
                'status' => 'Terminée',
            ],
            [
                'id' => 'CMD00128',
                'delivery_address' => 'Centre YDE F',
                'customer_name' => 'Fotso Jules',
                'items' => [
                    ['name' => 'Bouteille de 9kg', 'quantity' => 8, 'price' => 50.00],
                    ['name' => 'Bouteille de 12kg', 'quantity' => 4, 'price' => 65.00],
                ],
                'total_amount' => 660.00,
                'delivery_man' => 'Ngeufack Jean',
                'order_date' => '29/04/2025',
                'status' => 'Terminée',
            ],
        ];

        $order = collect($orders)->firstWhere('id', $order_id);

        if ($order) {
            return view('orders.order-details', compact('order'));
        } else {
            // Optionally handle the case where the order is not found
            abort(404, 'Order not found');
        }
    }
}
