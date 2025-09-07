<?php

declare(strict_types=1);

namespace App\Http\Api\Controllers\DistributionCenter;

use App\Http\Api\Requests\DistributionCenter\GetClosestDistributionCenterRequest;
use App\Http\Api\Resources\DistributionCenterResource;
use App\Http\Api\Resources\ProductResource;
use App\Http\Api\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\DistributionCenter\DistributionCenterService;

// TODO: update this to be sure that we have at least one distribution center in the same municipality as the customer delivery address
final class GetClosestDistributionCenterController extends Controller
{
    public function __construct(private readonly DistributionCenterService $distributionCenterService) {}

    /**
     * Find the closest distribution center to a given latitude and longitude or neighborhood.
     * Includes the center's available products.
     *
     * Route: GET /distribution-centers/closest
     * Name: api.distribution-centers.closest
     */
    public function __invoke(GetClosestDistributionCenterRequest $request): ApiResponse
    {
        if ($request->has('neighborhood_id')) {
            // Find by neighborhood ID
            $closestCenter = $this->distributionCenterService->findClosestByNeighborhood(
                (int) $request->input('neighborhood_id')
            );
        } else {
            // Find by coordinates
            $closestCenter = $this->distributionCenterService->findClosest(
                (float) $request->input('latitude'),
                (float) $request->input('longitude')
            );
        }

        if (! $closestCenter) {
            return ApiResponse::error(
                message: 'Aucun centre de distribution trouvé.',
                statusCode: 404
            );
        }

        // Get products for this distribution center
        $products = $this->distributionCenterService->getProducts($closestCenter->id);

        return ApiResponse::success(
            data: [
                'distribution_center' => new DistributionCenterResource($closestCenter),
                'products' => ProductResource::collection($products),
            ],
            message: __('messages.closest_distribution_center_found')
        );
    }
}
