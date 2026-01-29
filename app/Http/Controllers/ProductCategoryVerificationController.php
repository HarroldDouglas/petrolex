<?php

namespace App\Http\Controllers;

use App\Models\AccessoryType;
use App\Models\BottleType;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductCategoryVerificationController extends Controller
{
    /**
     * Display the product category verification page.
     *
     * Route: GET /products/verify-categories
     * Name: products.verify-categories
     */
    public function __invoke(Request $request)
    {
        // Statistiques
        $stats = [
            'total_categories' => ProductCategory::count(),
            'bottle_categories' => ProductCategory::bottles()->count(),
            'accessory_categories' => ProductCategory::accessories()->count(),
            'bottle_types' => BottleType::count(),
            'accessory_types' => AccessoryType::count(),
        ];

        // Vérification des bouteilles
        $bottles = BottleType::all()->map(function ($bottle) {
            $category = ProductCategory::bottles()
                ->where('product_type_id', $bottle->id)
                ->first();

            return [
                'id' => $bottle->id,
                'name' => $bottle->name,
                'category_id' => $category?->id,
                'has_category' => $category !== null,
            ];
        });

        // Vérification des accessoires
        $accessories = AccessoryType::all()->map(function ($accessory) {
            $category = ProductCategory::accessories()
                ->where('product_type_id', $accessory->id)
                ->first();

            return [
                'id' => $accessory->id,
                'name' => $accessory->name,
                'category_id' => $category?->id,
                'has_category' => $category !== null,
            ];
        });

        // Compter les problèmes
        $bottle_missing = $bottles->where('has_category', false)->count();
        $accessory_missing = $accessories->where('has_category', false)->count();
        $total_missing = $bottle_missing + $accessory_missing;

        return view('products.verify-categories', [
            'stats' => $stats,
            'bottles' => $bottles,
            'accessories' => $accessories,
            'bottle_missing' => $bottle_missing,
            'accessory_missing' => $accessory_missing,
            'total_missing' => $total_missing,
        ]);
    }
}
