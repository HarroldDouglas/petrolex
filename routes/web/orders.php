<?php

/**
 * Routes for the Order routes management
 *
 * @author  "Afrik Solutions" <contact@afrik-solutions.com>
 * @license MIT License
 */

use App\Http\Controllers\MapController;
use App\Http\Controllers\Order\CreateOrderController;
use App\Http\Controllers\Order\DeleteOrderController;
use App\Http\Controllers\Order\EditOrderController;
use App\Http\Controllers\Order\GetOrderDetailsController;
use App\Http\Controllers\Order\GetOrderListController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'orders', 'as' => 'orders.'], function () {
    Route::get('/', GetOrderListController::class)->name('list');
    Route::get('/create', CreateOrderController::class)->name('create');
    Route::get('/{order_id}/edit', EditOrderController::class)->name('edit');
    Route::get('/{order_id}/details', GetOrderDetailsController::class)->name('details');
    Route::delete('/{order_id}/delete', DeleteOrderController::class)->name('delete');
});

// Route::post('/get-directions', [MapController::class, 'getDirections'])->name('get.directions');
