<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Services\DistributionCenter\DistributionCenterService;
use Illuminate\Http\Request;

class EditDistributionCenterController extends Controller
{
    public function __construct(private DistributionCenterService $service) {}

    /**
     * Show the form for editing the specified distribution center.
     *
     * Route: GET /distribution-centers/{center_id}/edit
     * Name: distribution-centers.edit
     */
    public function __invoke(Request $request, int $centerId)
    {
        $distributionCenter = $this->service->find($centerId);

        return view('distribution-center.edit', compact('distributionCenter'));
    }
}
