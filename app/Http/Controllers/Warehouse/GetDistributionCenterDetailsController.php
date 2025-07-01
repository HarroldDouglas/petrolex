<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Services\DistributionCenter\DistributionCenterService;
use Illuminate\Http\Request;

class GetDistributionCenterDetailsController extends Controller
{
    public function __construct(private DistributionCenterService $service) {}

    /**
     * Display the specified distribution center details.
     *
     * Route: GET /distribution-centers/{center_id}/details
     * Name: distribution-centers.details
     */
    public function __invoke(Request $request, int $centerId)
    {
        $distributionCenter = $this->service->findWithRelation($centerId);

        return view('distribution-center.details', compact('distributionCenter'));
    }
}
