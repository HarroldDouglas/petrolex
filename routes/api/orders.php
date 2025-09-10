<?php

use App\Http\Api\Controllers\Order\AddCustomerCommentToOrderController;
use App\Http\Api\Controllers\Order\CancelOrderController;
use App\Http\Api\Controllers\Order\DeliverOrderController;
use App\Http\Api\Controllers\Order\ScanEmptyBottleController;
use App\Http\Api\Controllers\Order\StoreOrderController;
use App\Http\Api\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('orders')->name('api.')->group(function () {
    Route::post('/', StoreOrderController::class)->name('orders.store');
    Route::post('/{order}/customer-feedback', AddCustomerCommentToOrderController::class)->name('orders.customer-feedback');
    Route::patch('/{order}/deliver', DeliverOrderController::class)->name('orders.deliver');
    Route::post('/{order}/scan-empty-bottle', ScanEmptyBottleController::class)->name('orders.scan-empty-bottle');
    Route::patch('/{order}/cancel', CancelOrderController::class)->name('orders.cancel');
    Route::post('/checkout', [OrderController::class, 'processCheckout'])->name('checkout');
    Route::post('/payment-callback', [OrderController::class, 'paymentCallback'])->name('api.payments.callback');
});
