<?php

use App\Http\Api\Controllers\Warehouse\GetDistributionCentersController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/distribution-centers', GetDistributionCentersController::class)->name('api.distribution-centers');
});
