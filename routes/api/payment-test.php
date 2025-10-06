<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PaymentTest\PaymentTestController;
use App\Http\Controllers\Api\PaymentTest\StatusTestController;
use App\Http\Controllers\Api\PaymentTest\CallbackTestController;
use App\Http\Controllers\Api\PaymentTest\LogStreamController;

/*
|--------------------------------------------------------------------------
| Payment Test API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('test/payment')->name('payment-test.')->group(function () {
    
    Route::group(['prefix' => '', 'as' => 'payment.'], function () {
        Route::post('/{provider}', [PaymentTestController::class, 'processPayment'])
            ->name('process')
            ->whereIn('provider', ['mtn', 'orange']);
    });
    
    Route::group(['prefix' => 'status', 'as' => 'status.'], function () {
        Route::get('/{provider}/{transactionId}', [StatusTestController::class, 'getPaymentStatus'])
            ->name('check')
            ->whereIn('provider', ['mtn', 'orange']);
    });
    
    Route::group(['prefix' => 'transactions', 'as' => 'transactions.'], function () {
        Route::get('/history', [StatusTestController::class, 'getTransactionHistory'])
            ->name('history');
    });
    
    Route::group(['prefix' => 'callbacks', 'as' => 'callbacks.'], function () {
        Route::get('/recent', [StatusTestController::class, 'getRecentCallbacks'])
            ->name('recent');
    });
    
    /*
    |--------------------------------------------------------------------------
    | Callback Handling Routes
    |--------------------------------------------------------------------------
    */
    Route::group(['prefix' => 'callback', 'as' => 'callback.'], function () {
        Route::post('/{provider}', [CallbackTestController::class, 'handleCallback'])
            ->name('handle')
            ->whereIn('provider', ['mtn', 'orange']);
        
        Route::group(['prefix' => 'external', 'as' => 'external.'], function () {
            Route::post('/mtn', [CallbackTestController::class, 'handleMTNExternalCallback'])
                ->name('mtn');
            
            Route::post('/orange', [CallbackTestController::class, 'handleOrangeExternalCallback'])
                ->name('orange');
        });
    });
    
    /*
    |--------------------------------------------------------------------------
    | Monitoring & Logging Routes
    |--------------------------------------------------------------------------
    */
    Route::group(['prefix' => 'logs', 'as' => 'logs.'], function () {
        Route::get('/recent', [LogStreamController::class, 'getRecentLogs'])
            ->name('recent');
        
        Route::get('/live', [LogStreamController::class, 'getLivePaymentLogs'])
            ->name('live');
        
        Route::get('/stats', [LogStreamController::class, 'getPaymentTestStats'])
            ->name('stats');
    });
    
    /*
    |--------------------------------------------------------------------------
    | System Utility Routes
    |--------------------------------------------------------------------------
    */
    Route::group(['prefix' => 'system', 'as' => 'system.'], function () {
        Route::get('/health', function () {
            return response()->json([
                'success' => true,
                'message' => 'Payment test environment is healthy',
                'timestamp' => now()->toISOString(),
                'environment' => app()->environment(),
                'available_providers' => ['MTN', 'ORANGE'],
                'test_modes' => ['sandbox', 'live'],
                'log_filters' => ['payment', 'test', 'callback', 'all']
            ]);
        })->name('health');
    });
});
