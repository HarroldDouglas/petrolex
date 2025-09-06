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
            data: $cities->map(function (City $city) {
                $city->load(['neighborhoods.municipality']);
                
                return [
                    'id' => $city->id,
                    'name' => $city->name,
                    'neighborhoods' => $city->neighborhoods->map(function ($neighborhood) {
                        return [
                            'id' => $neighborhood->id,
                            'name' => $neighborhood->name,
                            'municipality_id' => $neighborhood->municipality_id,
                            'municipality' => [
                                'id' => $neighborhood->municipality->id,
                                'name' => $neighborhood->municipality->name,
                            ],
                            'is_active' => $neighborhood->is_active,
                        ];
                    })->toArray(),
                ];
            })->toArray(),
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

        return ApiResponse::success(
            data: ['id' => $city->id, 'name' => $city->name, 'country' => $city->country],
            message: 'City retrieved successfully.'
        );
    }
}
