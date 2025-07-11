<?php

use App\Http\Api\Controllers\Customer\GetCustomersController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/customers', GetCustomersController::class)->name('api.customers');
});
