<?php

namespace App\Http\Api\Controllers\Geography;

use App\Http\Api\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Geography\Neighborhood;
use App\Services\Geography\NeighborhoodService;
use Illuminate\Http\Request;

class NeighborhoodController extends Controller
{
    public function __construct(protected NeighborhoodService $neighborhoodService) {}

    /**
     * Display a listing of the neighborhoods for a given city.
     */
    public function index(Request $request, int $cityId): ApiResponse
    {
        $neighborhoods = $this->neighborhoodService->getNeighborhoodsByCity($cityId);

        return ApiResponse::success(
            data: $neighborhoods->map(fn (Neighborhood $neighborhood) => ['id' => $neighborhood->id, 'name' => $neighborhood->name])->toArray(),
            message: 'Neighborhoods retrieved successfully.'
        );
    }

    /**
     * Display the specified neighborhood.
     */
    public function show(int $neighborhoodId): ApiResponse
    {
        $neighborhood = $this->neighborhoodService->find($neighborhoodId);

        return ApiResponse::success(
            data: ['id' => $neighborhood->id, 'name' => $neighborhood->name, 'city_id' => $neighborhood->municipality->city->id],
            message: 'Neighborhood retrieved successfully.'
        );
    }
}
