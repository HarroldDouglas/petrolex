<?php

use App\Http\Api\Controllers\Customer\GetCustomerController;
use App\Http\Api\Controllers\Customer\GetCustomersController;
use App\Http\Api\Controllers\Customer\StoreCustomerController;
use App\Http\Api\Controllers\Customer\StoreCustomerDeliveryAddressController;
use App\Http\Api\Controllers\Customer\UpdateCustomerDeliveryAddressController;
use Illuminate\Support\Facades\Route;

Route::prefix('customers')->name('api.')->group(function () {
    /*
     * Registration sends an OTP (SMS = real Twilio cost). This route hits the
     * same controller as POST /register/customer, so it must carry the same
     * throttle — otherwise it becomes an unthrottled bypass for SMS flooding.
     */
    Route::middleware('throttle:4,1')->group(function () {
        Route::post('/', StoreCustomerController::class)->name('customers.store');
    });

    /*
     * Reading customer records exposes personal data (names, phones, addresses)
     * of the whole base, so it is restricted to staff roles — never other customers.
     */
    Route::middleware(['auth:sanctum', 'role:super_admin|admin|manager|gas_manager|center_manager'])->group(function () {
        Route::get('/', GetCustomersController::class)->name('customers.index');
        Route::get('/{customerId}', GetCustomerController::class)->name('customers.show');
    });
});

// Routes for authenticated user's delivery addresses (outside customers prefix)
Route::middleware('auth:sanctum')->prefix('my')->name('api.my.')->group(function () {
    Route::post('/delivery-addresses', StoreCustomerDeliveryAddressController::class)->name('delivery-addresses.store');
    Route::put('/delivery-addresses/{deliveryAddress}', UpdateCustomerDeliveryAddressController::class)->name('delivery-addresses.update');
});
