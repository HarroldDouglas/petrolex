<?php

use App\Http\Api\Controllers\TrackingDelivery\CompleteDeliveryTrackingController;
use App\Http\Api\Controllers\TrackingDelivery\CreateDeliveryTrackingController;
use App\Http\Api\Controllers\TrackingDelivery\GetDeliveryTrackingDetailsController;
use App\Http\Api\Controllers\TrackingDelivery\SimulateDeliveryController;
use App\Http\Api\Controllers\TrackingDelivery\SimulationStatusController;
use App\Http\Api\Controllers\TrackingDelivery\StartDeliveryTrackingController;
use App\Http\Api\Controllers\TrackingDelivery\UpdateDeliveryTrackingPositionController;
use Illuminate\Support\Facades\Route;

Route::prefix('tracking/delivery')->name('tracking.delivery.')->middleware(['auth:sanctum'])->group(function () {
    Route::post('/', CreateDeliveryTrackingController::class)->name('create');
    Route::post('/{orderId}/start', StartDeliveryTrackingController::class)->name('start');
    Route::patch('/{orderId}/position', UpdateDeliveryTrackingPositionController::class)->name('position.update');
    Route::get('/{orderId}', GetDeliveryTrackingDetailsController::class)->name('details');
    Route::patch('/{orderId}/complete', CompleteDeliveryTrackingController::class)->name('complete');
});

// 🎭 HIDDEN SIMULATION ENDPOINTS - NOT IN SWAGGER DOCUMENTATION
// These endpoints are exclusively for production demonstrations
Route::prefix('demo')->name('demo.')->middleware(['auth:sanctum'])->group(function () {
    Route::match(['GET', 'POST'], '/simulate-delivery/{orderId}', SimulateDeliveryController::class)->name('simulate.delivery');
    Route::get('/simulation/{orderId}/status', [SimulationStatusController::class, 'status'])->name('simulation.status');
    Route::delete('/simulation/{orderId}/stop', [SimulationStatusController::class, 'stop'])->name('simulation.stop');
    Route::get('/simulations/active', [SimulationStatusController::class, 'listActive'])->name('simulations.active');
});
