<?php

use App\Http\Api\Controllers\DeliveryPerson\GetDeliveryPersonOrdersController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->name('api.')->group(function () {
    Route::prefix('delivery-persons')->name('delivery-persons.')->group(function () {
        Route::get('/{deliveryPerson}/orders', GetDeliveryPersonOrdersController::class)->name('orders');
    });
});
