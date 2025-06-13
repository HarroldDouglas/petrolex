<?php

use App\Http\Controllers\Bottle\CreateBottleTypeController;
use App\Http\Controllers\Bottle\EditBottleTypeController;
use App\Http\Controllers\Bottle\GetBottleListController;
use App\Http\Controllers\Bottle\GetBottlesTypesController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::prefix('bottles')->name('bottles.')->group(function () {
        Route::get('/', GetBottleListController::class)->name('index');

        Route::prefix('types')->name('types.')->group(function () {
            Route::get('/', GetBottlesTypesController::class)->name('index');
            Route::get('/create', CreateBottleTypeController::class)->name('create');
            Route::get('/edit/{bottleType}', EditBottleTypeController::class)->name('edit');
        });
    });
});
