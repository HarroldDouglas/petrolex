<?php

use App\Http\Api\Controllers\Payment\GetPaymentMethodsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/payment-methods', GetPaymentMethodsController::class);
});
