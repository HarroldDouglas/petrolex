<?php

use App\Http\Controllers\ProductCategoryVerificationController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/verify-categories', ProductCategoryVerificationController::class)->name('verify-categories');
    });
});
