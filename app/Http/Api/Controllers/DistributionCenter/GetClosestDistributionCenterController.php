<?php

declare(strict_types=1);

namespace App\Http\Api\Controllers\DistributionCenter;

use App\Http\Api\Requests\DistributionCenter\GetClosestDistributionCenterRequest;
use App\Http\Api\Resources\DistributionCenterResource;
use App\Http\Api\Responses\Warehouse\DistributionCenterResponse;
use App\Http\Controllers\Controller;
use App\Services\DistributionCenter\DistributionCenterService;

final class GetClosestDistributionCenterController extends Controller
{
    public function __construct(private readonly DistributionCenterService $distributionCenterService) {}

    /**
     * Handle the incoming request to find the closest distribution center.
     *
     * @param  GetClosestDistributionCenterRequest  $request
     * @return DistributionCenterResponse
     */
    public function __invoke(GetClosestDistributionCenterRequest $request): DistributionCenterResponse
    {
        $closestCenter = $this->distributionCenterService->findClosest(
            (float) $request->input('latitude'),
            (float) $request->input('longitude')
        );

        if (! $closestCenter) {
            return DistributionCenterResponse::error('Aucun centre de distribution trouvé.', null, 404);
        }

        return DistributionCenterResponse::success(new DistributionCenterResource($closestCenter), 'Centre de distribution le plus proche trouvé.');
    }
}
