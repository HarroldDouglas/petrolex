<?php

/**
 * Routes for the Order routes management
 *
 * @author  "Afrik Solutions" <contact@afrik-solutions.com>
 * @license MIT License
 */

use App\Http\Controllers\Order\CancelOrderController;
use App\Http\Controllers\Order\GetOrderDetailsController;
use App\Http\Controllers\Order\GetOrderListController;
use App\Http\Controllers\Order\PrintOrderController;
use App\Http\Controllers\Order\RealTimeTrackingController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'orders', 'as' => 'orders.'], function () {
    Route::get('/', GetOrderListController::class)->name('list');
    Route::get('/{order}/details', GetOrderDetailsController::class)->name('details');
    Route::patch('/{order}/cancel', CancelOrderController::class)->name('cancel');
    Route::get('/{order}/track-real-time', RealTimeTrackingController::class)->name('track.realtime');

    Route::get('/{order}/print', PrintOrderController::class)
        ->name('print.ticket');

    Route::get('/{order}/download/invoice', [PrintOrderController::class, 'downloadPdf'])
        ->name('download.invoice');
});
