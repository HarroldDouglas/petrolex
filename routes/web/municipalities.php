<?php

use App\Http\Controllers\Municipality\CreateMunicipalityController;
use App\Http\Controllers\Municipality\EditMunicipalityController;
use App\Http\Controllers\Municipality\GetMunicipalityController;
use Illuminate\Support\Facades\Route;

Route::prefix('municipalities')->name('municipalities.')->group(function () {
    Route::get('/', GetMunicipalityController::class)->name('index');
    Route::get('/create', CreateMunicipalityController::class)->name('create');
    Route::get('{municipality}/edit', EditMunicipalityController::class)->name('edit');
});
