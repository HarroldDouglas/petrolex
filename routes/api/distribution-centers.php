<?php

use App\Http\Api\Controllers\DistributionCenter\GetClosestDistributionCenterController;
use App\Http\Api\Controllers\DistributionCenter\GetProductsController;
use App\Http\Api\Controllers\Warehouse\GetDistributionCentersController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->name('api.')->group(function () {
    Route::prefix('distribution-centers')->name('distribution-centers.')->group(function () {
        Route::get('/', GetDistributionCentersController::class)->name('index');
        Route::get('/closest', GetClosestDistributionCenterController::class)->name('closest');
        Route::get('/{id}/products', GetProductsController::class)->name('products');
    });
});
