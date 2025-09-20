<?php

use App\Http\Api\Controllers\Payment\GetPaymentMethodsController;
use App\Http\Api\Controllers\Payment\PaymentCallbackController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/payment-methods', GetPaymentMethodsController::class);
});

// Payment callback - no authentication required (external gateway calls)
Route::post('/payments/callback', PaymentCallbackController::class)->name('api.payments.callback');
