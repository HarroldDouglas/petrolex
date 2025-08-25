<?php

use App\Http\Api\Controllers\Customer\GetCustomerController;
use App\Http\Api\Controllers\Customer\GetCustomerOrdersController;
use App\Http\Api\Controllers\Customer\GetCustomersController;
use App\Http\Api\Controllers\Customer\StoreCustomerController;
use App\Http\Api\Controllers\Customer\StoreCustomerDeliveryAddressController;
use Illuminate\Support\Facades\Route;

Route::prefix('customers')->name('api.')->group(function () {
    Route::post('/', StoreCustomerController::class)->name('customers.store');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/', GetCustomersController::class)->name('customers.index');
        Route::get('/', GetCustomersController::class)->name('*customers');
        Route::get('/{customerId}', GetCustomerController::class)->name('customers.show');
        Route::get('/{customer}/orders', GetCustomerOrdersController::class)->name('customers.orders.index');
        Route::post('/{customer}/delivery-addresses', StoreCustomerDeliveryAddressController::class)->name('customers.delivery-addresses.store');
    });
});
