<?php

use App\Http\Api\Controllers\Order\AddCustomerCommentToOrderController;
use App\Http\Api\Controllers\Order\StoreOrderController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('orders')->name('api.')->group(function () {
    Route::post('/', StoreOrderController::class)->name('orders.store');
    Route::post('/{order}/customer-feedback', AddCustomerCommentToOrderController::class)->name('orders.customer-feedback');
});
