<?php

use App\Http\Controllers\Neighborhood\CreateNeighborhoodController;
use App\Http\Controllers\Neighborhood\EditNeighborhoodController;
use App\Http\Controllers\Neighborhood\GetNeighborhoodsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::prefix('neighborhoods')->name('neighborhoods.')->group(function () {
        Route::get('/', GetNeighborhoodsController::class)->name('index');
        Route::get('/create', CreateNeighborhoodController::class)->name('create');
        Route::get('/edit/{neighborhood}', EditNeighborhoodController::class)->name('edit');
    });
});
