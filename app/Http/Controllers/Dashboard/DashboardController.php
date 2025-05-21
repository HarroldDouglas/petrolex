<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Données statiques pour le mock-up
        $orders = [
            [
                'id' => 'CMD00120',
                'delivery_address' => 'Point YDE B',
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
                'delivery_address' => 'Point YDE C',
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
                'delivery_address' => 'Point YDE D',
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
                'delivery_address' => 'Point YDE E',
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
                'delivery_address' => 'Point YDE F',
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
                'delivery_address' => 'Point YDE F',
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
                'delivery_address' => 'Point YDE F',
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
                'delivery_address' => 'Point YDE F',
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
                'delivery_address' => 'Point YDE F',
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
        
        // Types de bouteilles et statuts pour les filtres
        $itemTypes = ['Bouteille 9kg', 'Bouteille 12kg'];
        $statuses = ['En cours', 'Terminée'];

        // Retourner la vue avec les données
        return view('dashboard.index', compact('orders', 'itemTypes', 'statuses'));
    }
}
