<?php

use App\Http\Controllers\Product\CreateProductController;
use App\Http\Controllers\Product\DetailsProductController;
use App\Http\Controllers\Product\EditProductController;
use App\Http\Controllers\Product\GetProductsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('products/create', CreateProductController::class)->name('products.create');
    Route::get('products/edit', EditProductController::class)->name('products.edit');
    Route::get('products', GetProductsController::class)->name('products.list');
    Route::get('products/details', DetailsProductController::class)->name('products.details');
});
