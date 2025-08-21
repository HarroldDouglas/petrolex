<?php

use App\Http\Api\Controllers\Geography\CityController;
use App\Http\Api\Controllers\Geography\NeighborhoodController;
use App\Http\Api\Controllers\Geography\MunicipalityController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('geography')->name('api.geography.')->group(function () {
    // Cities
    Route::get('/countries/{country}/cities', [CityController::class, 'index'])->name('cities.index');
    Route::get('/cities/{cityId}', [CityController::class, 'show'])->name('cities.show');

    // Neighborhoods
    Route::get('/cities/{cityId}/neighborhoods', [NeighborhoodController::class, 'index'])->name('neighborhoods.index');
    Route::get('/neighborhoods/{neighborhoodId}', [NeighborhoodController::class, 'show'])->name('neighborhoods.show');

    // Municipalities
    Route::apiResource('/municipalities', MunicipalityController::class);
    Route::post('/municipalities/{municipality}/neighborhoods', [MunicipalityController::class, 'syncNeighborhoods'])->name('municipalities.syncNeighborhoods');
});
