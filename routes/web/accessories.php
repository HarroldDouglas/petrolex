<?php

use App\Http\Controllers\Accessory\CreateAccessoryController;
use App\Http\Controllers\Accessory\EditAccessoryController;
use App\Http\Controllers\Accessory\GetAccessoriesController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::prefix('accessories')->name('accessories.')->group(function () {
        Route::get('/', GetAccessoriesController::class)->name('index');
        Route::get('/create', CreateAccessoryController::class)->name('create');
        Route::get('/edit/{id}', EditAccessoryController::class)->name('edit');
    });
});
