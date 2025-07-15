<?php

use App\Http\Api\Controllers\Customer\GetCustomersController;
use App\Http\Api\Controllers\Customer\StoreCustomerDeliveryAddressController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/customers', GetCustomersController::class)->name('api.customers');
    Route::post('/customers/{customer}/delivery-addresses', StoreCustomerDeliveryAddressController::class)->name('api.customers.delivery-addresses.store');
});
