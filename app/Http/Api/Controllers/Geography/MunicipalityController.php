<?php

namespace App\Http\Api\Controllers\Geography;

use App\Http\Api\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Municipality;
use App\Services\Geography\MunicipalityService;
use Illuminate\Http\Request;

class MunicipalityController extends Controller
{
    public function __construct(protected MunicipalityService $municipalityService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): ApiResponse
    {
        $municipalities = $this->municipalityService->getAllMunicipalities();

        return ApiResponse::success(
            data: $municipalities->map(fn (Municipality $municipality) => ['id' => $municipality->id, 'name' => $municipality->name, 'city_id' => $municipality->city_id])->toArray(),
            message: 'Municipalities retrieved successfully.'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): ApiResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'city_id' => 'required|exists:cities,id',
            'neighborhood_ids' => 'array',
            'neighborhood_ids.*' => 'exists:neighborhoods,id',
        ]);

        $municipality = $this->municipalityService->createMunicipality(
            $request->only(['name', 'city_id']),
            $request->input('neighborhood_ids', [])
        );

        return ApiResponse::success(
            data: ['id' => $municipality->id, 'name' => $municipality->name],
            message: 'Municipality created successfully.',
            statusCode: 201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(int $municipalityId): ApiResponse
    {
        $municipality = $this->municipalityService->find($municipalityId);

        return ApiResponse::success(
            data: ['id' => $municipality->id, 'name' => $municipality->name, 'city_id' => $municipality->city_id, 'neighborhoods' => $municipality->neighborhoods->pluck('id')->toArray()],
            message: 'Municipality retrieved successfully.'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $municipalityId): ApiResponse
    {
        $municipality = $this->municipalityService->find($municipalityId);

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'city_id' => 'sometimes|required|exists:cities,id',
            'neighborhood_ids' => 'array',
            'neighborhood_ids.*' => 'exists:neighborhoods,id',
        ]);

        $municipality = $this->municipalityService->updateMunicipality(
            $municipality,
            $request->only(['name', 'city_id']),
            $request->input('neighborhood_ids', [])
        );

        return ApiResponse::success(
            data: ['id' => $municipality->id, 'name' => $municipality->name],
            message: 'Municipality updated successfully.'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $municipalityId): ApiResponse
    {
        $municipality = $this->municipalityService->find($municipalityId);

        $this->municipalityService->deleteMunicipality($municipality);

        return ApiResponse::success(message: 'Municipality deleted successfully.');
    }

    /**
     * Sync neighborhoods for a given municipality.
     */
    public function syncNeighborhoods(Request $request, int $municipalityId): ApiResponse
    {
        $municipality = $this->municipalityService->find($municipalityId);

        $request->validate([
            'neighborhood_ids' => 'required|array',
            'neighborhood_ids.*' => 'exists:neighborhoods,id',
        ]);

        $this->municipalityService->updateMunicipality(
            $municipality,
            [], // No attributes to update, just sync neighborhoods
            $request->input('neighborhood_ids')
        );

        return ApiResponse::success(message: 'Neighborhoods synced successfully.');
    }
}
