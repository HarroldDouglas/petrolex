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

// Client-side JS log collector (for remote debugging on mobile)
Route::post('/js-log', function (Request $request) {
    $entries = $request->input('entries', []);
    $ua = $request->userAgent();
    $ip = $request->ip();

    foreach (array_slice($entries, 0, 50) as $entry) {
        \Illuminate\Support\Facades\Log::channel('jslog')->info($entry['message'] ?? '', [
            'level' => $entry['level'] ?? 'log',
            'url' => $entry['url'] ?? '',
            'ua' => $ua,
            'ip' => $ip,
        ]);
    }

    return response()->json(['ok' => true]);
});

// Inclure les routes modulaires
require __DIR__.'/api/auth.php';
require __DIR__.'/api/distribution-centers.php';
require __DIR__.'/api/customers.php';
require __DIR__.'/api/orders.php';
require __DIR__.'/api/bottles.php';
require __DIR__.'/api/payments.php';
require __DIR__.'/api/delivery.php';
require __DIR__.'/api/tracking.php';
require __DIR__.'/api/geography.php';
require __DIR__.'/api/app.php';

if (! app()->isProduction()) {
    Route::post('test-websocket-event', function () {
        event(new \App\Events\TestWebSocketEvent);

        return response()->json([
            'success' => true,
            'message' => 'Test WebSocket event dispatched',
            'time' => now()->toISOString(),
        ]);
    });
}
