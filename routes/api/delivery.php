<?php

use App\Http\Api\Controllers\Delivery\GetDeliveryTypesController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/delivery-types', GetDeliveryTypesController::class);
});
