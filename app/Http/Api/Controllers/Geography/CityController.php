<?php

namespace App\Http\Api\Controllers\Geography;

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

        return ApiResponse::success(
            data: $cities->map(fn (City $city) => ['id' => $city->id, 'name' => $city->name])->toArray(),
            message: 'Cities retrieved successfully.'
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

        return ApiResponse::success(
            data: ['id' => $city->id, 'name' => $city->name, 'country' => $city->country],
            message: 'City retrieved successfully.'
        );
    }
}
