<?php

use App\Http\Controllers\Bottle\CreateBottleTypeController;
use App\Http\Controllers\Bottle\EditBottleTypeController;
use App\Http\Controllers\Bottle\GetBottleListController;
use App\Http\Controllers\Bottle\GetBottlesTypesController;
use App\Http\Controllers\Bottle\GetBottleHistoryController;
use App\Http\Controllers\Bottle\MarkBottleFoundController;
use App\Http\Controllers\Bottle\MarkBottleLostController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('bottles/types/create', CreateBottleTypeController::class)->name('bottles.types.create');
    Route::get('bottles/types/edit/{bottle_id}', EditBottleTypeController::class)->name('bottles.types.edit');
    Route::get('bottles', GetBottleListController::class)->name('bottles.list');
    Route::get('bottles/types', GetBottlesTypesController::class)->name('bottles.types');
    Route::get('bottles/history/{bottle_id}', GetBottleHistoryController::class)->name('bottles.history');
    Route::post('/bottles/{id}/mark-found', MarkBottleFoundController::class)->name('bottles.mark-found');
    Route::post('/bottles/{id}/mark-lost', MarkBottleLostController::class)->name('bottles.mark-lost');
});
