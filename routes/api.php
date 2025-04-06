<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        '_metadata' => [
            'success' => true,
            'message' => 'API is running',
        ],
        'data' => [
            'status' => 'healthy',
            'timestamp' => now()->toISOString(),
        ],
    ]);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Inclure les routes modulaires
require __DIR__.'/api/auth.php';
require __DIR__.'/api/customer.php';
require __DIR__.'/api/deliverer.php';
require __DIR__.'/api/controller.php';
require __DIR__.'/api/orders.php';
require __DIR__.'/api/bottles.php';
require __DIR__.'/api/payments.php';
require __DIR__.'/api/tracking.php';
