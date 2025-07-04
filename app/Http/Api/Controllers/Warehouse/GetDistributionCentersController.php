<?php

namespace App\Http\Api\Controllers\Warehouse;

use App\Http\Api\Responses\Warehouse\DistributionCenterResponse;
use App\Http\Controllers\Controller;
use App\Services\DistributionCenter\DistributionCenterService;
use Illuminate\Support\Facades\Log;

class GetDistributionCentersController extends Controller
{
    public function __construct(protected DistributionCenterService $distributionCenterService) {}

    /**
     * Get all distribution-centers.
     *
     * Route: GET /distribution-centers
     * Name: api.distribution-centers
     */
    public function __invoke(): DistributionCenterResponse
    {
        $distributionCenters = $this->distributionCenterService->getAll();
        Log::info('$distributionCenters', [$distributionCenters]);

        return DistributionCenterResponse::withDistributionCenters($distributionCenters);
    }
}
