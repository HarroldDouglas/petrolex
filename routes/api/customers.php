<?php

use App\Http\Api\Controllers\Customer\GetCustomerController;
use App\Http\Api\Controllers\Customer\GetCustomersController;
use App\Http\Api\Controllers\Customer\StoreCustomerDeliveryAddressController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('customers')->name('api.')->group(function () {
    Route::get('/', GetCustomersController::class)->name('customers.index');
    Route::get('/{customerId}', GetCustomerController::class)->name('customers.show');
    Route::post('/{customer}/delivery-addresses', StoreCustomerDeliveryAddressController::class)->name('customers.delivery-addresses.store');
});
