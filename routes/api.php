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
require __DIR__.'/api/distribution-centers.php';
require __DIR__.'/api/customers.php';
require __DIR__.'/api/deliverer.php';
require __DIR__.'/api/orders.php';
require __DIR__.'/api/bottles.php';
require __DIR__.'/api/payments.php';
require __DIR__.'/api/delivery.php';
require __DIR__.'/api/tracking.php';
require __DIR__.'/api/geography.php';
require __DIR__.'/api/app.php';

// Payment Test Routes (isolated)
require __DIR__.'/api/payment-test.php';

// Log streaming endpoints (moved to payment test routes)
// Note: Log endpoints are now available at /api/test/payment/logs/

// External callback routes (for compatibility with external server callbacks)
// Note: These routes are now handled in payment-test.php but kept here for backward compatibility
Route::post('/callback/cm/momo', function () {
    return response()->json([
        'success' => false,
        'message' => 'This endpoint has been moved to /api/test/payment/callback/external/mtn',
    ], 301);
})->name('callback.mtn.deprecated');

Route::post('/callback/cm/orange', function () {
    return response()->json([
        'success' => false,
        'message' => 'This endpoint has been moved to /api/test/payment/callback/external/orange',
    ], 301);
})->name('callback.orange.deprecated');

// Route de test WebSocket
Route::post('test-websocket-event', function () {
    event(new \App\Events\TestWebSocketEvent);

    return response()->json([
        'success' => true,
        'message' => 'Test WebSocket event dispatched',
        'time' => now()->toISOString(),
    ]);
});
