<?php

use App\Http\Api\Controllers\Customer\GetCustomerController;
use App\Http\Api\Controllers\Customer\GetCustomersController;
use App\Http\Api\Controllers\Customer\StoreCustomerController;
use App\Http\Api\Controllers\Customer\StoreCustomerDeliveryAddressController;
use App\Http\Api\Controllers\Customer\UpdateCustomerDeliveryAddressController;
use Illuminate\Support\Facades\Route;

Route::prefix('customers')->name('api.')->group(function () {
    Route::post('/', StoreCustomerController::class)->name('customers.store');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/', GetCustomersController::class)->name('customers.index');
        Route::get('/', GetCustomersController::class)->name('*customers');
        Route::get('/{customerId}', GetCustomerController::class)->name('customers.show');
    });
});

// Routes for authenticated user's delivery addresses (outside customers prefix)
Route::middleware('auth:sanctum')->prefix('my')->name('api.my.')->group(function () {
    Route::post('/delivery-addresses', StoreCustomerDeliveryAddressController::class)->name('delivery-addresses.store');
    Route::put('/delivery-addresses/{deliveryAddress}', UpdateCustomerDeliveryAddressController::class)->name('delivery-addresses.update');
});
