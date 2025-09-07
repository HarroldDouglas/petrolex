<?php

namespace App\Http\Api\Controllers\Geography;

use App\Http\Api\Resources\CityResource;
use App\Http\Api\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Geography\City;
use App\Services\Geography\CityService;
use Illuminate\Http\Request;

class CityController extends Controller
{
    public function __construct(protected CityService $cityService) {}

    /**
     * Display a listing of the cities for a given country.
     */
    public function index(Request $request, string $country): ApiResponse
    {
        $cities = $this->cityService->getCitiesByCountry($country);

        // Load necessary relationships for the Resources
        $cities->load(['neighborhoods.municipality']);

        return ApiResponse::success(
            data: CityResource::collection($cities),
            message: 'Cities with neighborhoods retrieved successfully.'
        );
    }

    /**
     * Display the specified city.
     */
    public function show(int $cityId): ApiResponse
    {
        /**
         * @var City $city
         */
        $city = $this->cityService->find($cityId);

        // Load country relationship for the Resource
        $city->load(['country']);

        return ApiResponse::success(
            data: new CityResource($city),
            message: 'City retrieved successfully.'
        );
    }
}
