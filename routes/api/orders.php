<?php

use App\Http\Api\Controllers\Order\CancelOrderController;
use App\Http\Api\Controllers\Order\DeliverOrderController;
use App\Http\Api\Controllers\Order\ScanEmptyBottleController;
use App\Http\Api\Controllers\Order\StoreOrderController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('orders')->name('api.')->group(function () {
    Route::post('/', StoreOrderController::class)->name('orders.store');
    Route::patch('/{order}/deliver', DeliverOrderController::class)->name('orders.deliver');
    Route::post('/{order}/scan-empty-bottle', ScanEmptyBottleController::class)->name('orders.scan-empty-bottle');
    Route::patch('/{order}/cancel', CancelOrderController::class)->name('orders.cancel');
});
