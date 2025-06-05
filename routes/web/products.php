<?php

use App\Http\Controllers\Product\CreateProductController;
use App\Http\Controllers\Product\DetailsProductController;
use App\Http\Controllers\Product\EditProductController;
use App\Http\Controllers\Product\GetProductsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', GetProductsController::class)->name('list');
        Route::get('/create', CreateProductController::class)->name('create');
        Route::get('/edit', EditProductController::class)->name('edit');
        Route::get('/details', DetailsProductController::class)->name('details');
    });
});
