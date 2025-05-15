<?php

namespace App\Http\Controllers\Bottle;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GetBottleListController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        // Données statiques pour le mock-up
        $bottles = [
            [
                'id' => 1,
                'barcode' => 'BTL100001',
                'type' => 'Bouteille 6kg',
                'created_at' => '2023-06-18 08:30:00',
                'status' => 'En stock',
            ],
            [
                'id' => 2,
                'barcode' => 'BTL100002',
                'type' => 'Bouteille 9kg',
                'created_at' => '2023-06-17 14:15:00',
                'status' => 'Vendu',
            ],
            [
                'id' => 3,
                'barcode' => 'BTL100003',
                'type' => 'Bouteille 12.5kg',
                'created_at' => '2023-06-16 09:45:00',
                'status' => 'Perdu',
            ],
            [
                'id' => 4,
                'barcode' => 'BTL100004',
                'type' => 'Bouteille 6kg',
                'created_at' => '2023-06-15 16:20:00',
                'status' => 'En cours de livraison',
            ],
            [
                'id' => 5,
                'barcode' => 'BTL100005',
                'type' => 'Bouteille 9kg',
                'created_at' => '2023-06-14 11:10:00',
                'status' => 'Livré',
            ],
        ];

        // Types de bouteilles et statuts pour les filtres
        $bottleTypes = ['Bouteille 6kg', 'Bouteille 9kg', 'Bouteille 12.5kg'];
        $statuses = ['En stock', 'Vendu', 'Perdu', 'En cours de livraison', 'Livré'];

        // Retourner la vue avec les données
        return view('bottles.bottle-list', compact('bottles', 'bottleTypes', 'statuses'));
    }
}
