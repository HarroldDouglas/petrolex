<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GetProductsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        // Données statiques pour le mock-up
        $bottleTypes = [
            [
                'id' => 1,
                'name' => 'Détenteurs',
                'categorie' => 'Accessoires',
                'status' => 'Actif',
                'created_at' => '2023-06-18 08:30:00',
            ],
            [
                'id' => 2,
                'name' => 'Bouteille 9kg',
                'status' => 'Actif',
                'created_at' => '2023-06-17 14:15:00',
            ],
            [
                'id' => 3,
                'name' => 'Bouteille 12.5kg',
                'status' => 'Inactif',
                'created_at' => '2023-06-16 09:45:00',
            ],
            [
                'id' => 4,
                'name' => 'Bouteille 35kg',
                'status' => 'Actif',
                'created_at' => '2023-06-15 16:20:00',
            ],
            [
                'id' => 5,
                'name' => 'Bouteille 45kg',
                'status' => 'Actif',
                'created_at' => '2023-06-14 11:10:00',
            ],
        ];

        // Retourner la vue avec les données
        return view('bottles.bottle-type-list', compact('bottleTypes'));
    }
}
