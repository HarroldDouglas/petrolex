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
        $products = [
            [
                'id' => 1,
                'name' => 'Détenteurs',
                'price' => '1500 - 2000',
                'status' => 'Actif',
                'stock' => 25,
                'created_at' => '2023-06-18 08:30:00',
            ],
            [
                'id' => 2,
                'name' => 'Câbles',
                'price' => '1000 - 1500',
                'status' => 'Actif',
                'stock' => 10,
                'created_at' => '2023-06-17 14:15:00',
            ],
            [
                'id' => 3,
                'name' => 'Extincteurs',
                'price' => '5000 - 10000',
                'status' => 'Inactif',
                'stock' => 0,
                'created_at' => '2023-06-16 09:45:00',
            ],
        ];

        // Retourner la vue avec les données
        return view('product.product-list', compact('products'));
    }
}
