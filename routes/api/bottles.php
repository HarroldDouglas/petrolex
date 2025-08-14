<?php

use App\Http\Api\Controllers\Bottle\VerifyBottleController; // Updated
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('bottles')->name('api.')->group(function () {
    Route::get('/{barcode}/verify', VerifyBottleController::class)->name('bottles.verify');
});
