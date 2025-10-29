<?php

use App\Http\Api\Controllers\GetMyOrdersController;
use App\Http\Api\Controllers\Order\AddCustomerCommentToOrderController;
use App\Http\Api\Controllers\Order\CancelOrderController;
use App\Http\Api\Controllers\Order\CreateOrderController;
use App\Http\Api\Controllers\Order\DeliverOrderController;
use App\Http\Api\Controllers\Order\DownloadInvoiceController;
use App\Http\Api\Controllers\Order\GetOrderDetailsController;
use App\Http\Api\Controllers\Order\ScanEmptyBottleController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('orders')->name('api.')->group(function () {
    Route::post('/', CreateOrderController::class)->name('orders.store');
    Route::get('/{order}', GetOrderDetailsController::class)->name('orders.show');
    Route::get('/{order}/download/invoice', DownloadInvoiceController::class)->name('orders.download.invoice');
    Route::post('/{order}/customer-feedback', AddCustomerCommentToOrderController::class)->name('orders.customer-feedback');
    Route::patch('/{order}/deliver', DeliverOrderController::class)->name('orders.deliver');
    Route::post('/{order}/scan-empty-bottle', ScanEmptyBottleController::class)->name('orders.scan-empty-bottle');
    Route::patch('/{order}/cancel', CancelOrderController::class)->name('orders.cancel');
    Route::post('/{order}/payment', \App\Http\Api\Controllers\Payment\InitiatePaymentController::class)->name('orders.initiate-payment');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/my/orders', GetMyOrdersController::class)->name('api.my.orders.index');
});
