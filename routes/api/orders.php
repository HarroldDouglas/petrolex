<?php

use App\Http\Api\Controllers\Order\GetOrdersController;
use App\Http\Api\Controllers\Order\StoreOrderController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('orders')->name('api.')->group(function () {
    Route::get('/', GetOrdersController::class)->name('orders.index');
    Route::post('/', StoreOrderController::class)->name('orders.store');
});
