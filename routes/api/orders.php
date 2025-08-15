<?php

use App\Http\Api\Controllers\Order\DeliverOrderController;
use App\Http\Api\Controllers\Order\ScanEmptyBottleController;
use App\Http\Api\Controllers\Order\StoreOrderController; // Added
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('orders')->name('api.')->group(function () {
    Route::post('/', StoreOrderController::class)->name('orders.store');
    Route::patch('/{order}/deliver', DeliverOrderController::class)->name('orders.deliver'); // Added
    Route::post('/{order}/scan-empty-bottle', ScanEmptyBottleController::class)->name('orders.scan-empty-bottle');
});
