<?php

use App\Http\Api\Controllers\Payment\GetPaymentMethodsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/payment-methods', GetPaymentMethodsController::class);
});

/*
 * NOTE: The public POST /payments/callback endpoint was removed for security.
 * It marked orders as PAID from unauthenticated input without verifying the
 * gateway signature or the amount. Payment confirmation is handled exclusively
 * server-side by App\Jobs\VerifyPaymentStatusJob, which pulls the real status
 * from the operator API (Orange/MTN) — a source that cannot be spoofed.
 */
