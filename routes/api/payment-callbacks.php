<?php

use App\Http\Controllers\Api\Payment\MTNCallbackController;
use App\Http\Controllers\Api\Payment\OrangeCallbackController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Payment Callback API Routes (Production)
|--------------------------------------------------------------------------
| These routes handle production callbacks from payment providers
|--------------------------------------------------------------------------
*/

Route::prefix('callback')->name('payment-callbacks.')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | MTN Mobile Money Callbacks
    |--------------------------------------------------------------------------
    */
    Route::group(['prefix' => 'cm', 'as' => 'cm.'], function () {
        Route::post('/momo', [MTNCallbackController::class, 'handleCallback'])
            ->name('momo');
    });

    /*
    |--------------------------------------------------------------------------
    | Orange Money Callbacks
    |--------------------------------------------------------------------------
    */
    Route::group(['prefix' => 'cm', 'as' => 'cm.'], function () {
        Route::post('/orange', [OrangeCallbackController::class, 'handleCallback'])
            ->name('orange');
    });

});
