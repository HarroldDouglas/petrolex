<?php

use App\Http\Api\Controllers\Order\CancelOrderController;
use App\Http\Api\Controllers\Order\DeliverOrderController;
use App\Http\Api\Controllers\Order\StoreOrderController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('orders')->name('api.')->group(function () {
    Route::post('/', StoreOrderController::class)->name('orders.store');
    Route::patch('/{order}/cancel', CancelOrderController::class)->name('orders.cancel');
    Route::patch('/{order}/deliver', DeliverOrderController::class)->name('orders.deliver');
});
