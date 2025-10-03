<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PaymentTestController;
use App\Http\Controllers\Api\LogStreamController;

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

// Payment Test Routes
Route::prefix('payment/test')->group(function () {
    Route::post('/{provider}', [PaymentTestController::class, 'processPayment'])
        ->where('provider', 'mtn|orange')
        ->name('payment.test.process');
    
    Route::get('/{provider}/status/{transactionId}', [PaymentTestController::class, 'getPaymentStatus'])
        ->where('provider', 'mtn|orange')
        ->name('payment.test.status');
    
    // Payment provider callbacks (no middleware needed for external calls)
    Route::post('/{provider}/callback', [PaymentTestController::class, 'handleCallback'])
        ->where('provider', 'mtn|orange')
        ->name('payment.test.callback');
    
    // Get recent callbacks for test console
    Route::get('/callbacks/recent', [PaymentTestController::class, 'getRecentCallbacks'])
        ->name('payment.test.callbacks.recent');
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

// Log streaming endpoints
Route::get('/logs/recent', [LogStreamController::class, 'getRecentLogs']);

// External callback routes (for compatibility with external server callbacks)
Route::post('/callback/cm/momo', [PaymentTestController::class, 'handleMTNExternalCallback'])
    ->name('callback.mtn');
Route::post('/callback/cm/orange', [PaymentTestController::class, 'handleOrangeExternalCallback'])
    ->name('callback.orange');

// Route de test WebSocket
Route::post('test-websocket-event', function () {
    event(new \App\Events\TestWebSocketEvent);

    return response()->json([
        'success' => true,
        'message' => 'Test WebSocket event dispatched',
        'time' => now()->toISOString(),
    ]);
});
