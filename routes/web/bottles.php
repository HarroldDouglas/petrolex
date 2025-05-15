<?php

use App\Http\Controllers\Bottle\CreateBottleTypeController;
use App\Http\Controllers\Bottle\EditBottleTypeController;
use App\Http\Controllers\Bottle\GetBottleListController;
use App\Http\Controllers\Bottle\GetBottlesTypesController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('bottles/types/create', CreateBottleTypeController::class)->name('bottles.types.create');
    Route::get('bottles/types/edit/{bottle_id}', EditBottleTypeController::class)->name('bottles.types.edit');
    Route::get('bottles/list', GetBottleListController::class)->name('bottles.list');
    Route::get('bottles/types', GetBottlesTypesController::class)->name('bottles.types');
});
