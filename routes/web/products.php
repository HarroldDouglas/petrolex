<?php

use App\Http\Controllers\Bottle\GetBottleListController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('products/create', GetBottleListController::class)->name('products.create');
    Route::get('products/edit', GetBottleListController::class)->name('products.edit');
    Route::get('products/list', GetBottleListController::class)->name('products.list');
    Route::get('products/details', GetBottleListController::class)->name('products.details');
});
