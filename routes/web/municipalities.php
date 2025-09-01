<?php

use App\Http\Controllers\MunicipalityController;
use Illuminate\Support\Facades\Route;

Route::prefix('municipalities')->name('municipalities.')->group(function () {
    Route::get('/', [MunicipalityController::class, 'index'])->name('index');
    Route::get('/create', [MunicipalityController::class, 'create'])->name('create');
    Route::get('{municipality}/edit', [MunicipalityController::class, 'edit'])->name('edit');
});
