<?php

use App\Http\Api\Controllers\TrackingDelivery\CompleteDeliveryTrackingController;
use App\Http\Api\Controllers\TrackingDelivery\CreateDeliveryTrackingController;
use App\Http\Api\Controllers\TrackingDelivery\GetActiveDeliveriesController;
use App\Http\Api\Controllers\TrackingDelivery\GetDeliveryTrackingDetailsController;
use App\Http\Api\Controllers\TrackingDelivery\StartDeliveryTrackingController;
use App\Http\Api\Controllers\TrackingDelivery\UpdateDeliveryTrackingPositionController;
use Illuminate\Support\Facades\Route;

Route::prefix('tracking/delivery')->name('tracking.delivery.')->group(function () {
    Route::get('/active', GetActiveDeliveriesController::class)->name('active');
    Route::post('/', CreateDeliveryTrackingController::class)->name('create');
    Route::post('/{orderId}/start', StartDeliveryTrackingController::class)->name('start');
    Route::patch('/{orderId}/position', UpdateDeliveryTrackingPositionController::class)->name('position.update');
    Route::get('/{orderId}', GetDeliveryTrackingDetailsController::class)->name('details');
    Route::patch('/{orderId}/complete', CompleteDeliveryTrackingController::class)->name('complete');
});
