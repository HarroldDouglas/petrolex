<?php

namespace App\Http\Api\Controllers\DistributionCenter;

use App\Http\Api\Responses\DistributionCenter\ProductResponse;
use App\Http\Controllers\Controller;
use App\Services\DistributionCenter\DistributionCenterService;
use Illuminate\Http\Request;

class GetProductsController extends Controller
{
    public function __construct(protected DistributionCenterService $distributionCenterService) {}

    /**
     * Get all products for a distribution-center.
     *
     * Route: GET /distribution-centers/{distributionCenterId}/products
     * Name: api.distribution-centers.products
     */
    public function __invoke(Request $request, int $distributionCenterId): ProductResponse
    {
        $products = $this->distributionCenterService->getProducts($distributionCenterId);

        $cityId = app(\App\Services\ProductCategoryService::class)
            ->resolveCityIdForDistributionCenter($distributionCenterId);

        return ProductResponse::withCollection($products, $cityId);
    }
}
