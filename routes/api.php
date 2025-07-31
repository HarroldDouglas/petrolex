<?php

use App\Http\Controllers\Api\Test\Delivery\CreateDeliveryTrackingController;
use App\Http\Controllers\Api\Test\Delivery\GetActiveDeliveriesController;
use App\Http\Controllers\Api\Test\Delivery\GetDeliveryTrackingDetailsController;
use App\Http\Controllers\Api\Test\Delivery\StartDeliveryTrackingController;
use App\Http\Controllers\Api\Test\Delivery\UpdateDeliveryTrackingPositionController;
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






Route::prefix('test/delivery')->name('test.delivery.')->group(function () {
    Route::get('/active', GetActiveDeliveriesController::class)->name('active');
    Route::post('/', CreateDeliveryTrackingController::class)->name('create');
    Route::post('/{orderNumber}/start', StartDeliveryTrackingController::class)->name('start');
    Route::patch('/{orderNumber}/position', UpdateDeliveryTrackingPositionController::class)->name('position.update');
    Route::get('/{orderNumber}', GetDeliveryTrackingDetailsController::class)->name('details');
});
