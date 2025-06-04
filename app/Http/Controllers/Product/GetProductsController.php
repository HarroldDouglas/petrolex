<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Services\Product\ProductService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class GetProductsController extends Controller
{
    public function __construct(
        private readonly ProductService $productService
    ) {}

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        $productStats = $this->productService->getProductStats();

        return view('product.product-list', [
            'productStats' => $productStats,
        ]);
    }
}
