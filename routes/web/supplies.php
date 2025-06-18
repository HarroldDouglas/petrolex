<?php

use App\Http\Controllers\Supply\CreateSupplyController;
use App\Http\Controllers\Supply\DeleteSupplyController;
use App\Http\Controllers\Supply\EditSupplyController;
use App\Http\Controllers\Supply\GetSupplyDetailsController;
use App\Http\Controllers\Supply\GetSupplyListController;
use App\Http\Controllers\Supply\RegisterProductsController;
use App\Http\Controllers\Supply\ScanBottlesController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->name('supplies.')->prefix('supplies')->
group(function () {
    Route::get('/', GetSupplyListController::class)->name('list');
    Route::get('/create', CreateSupplyController::class)->name('create');

    Route::prefix('{supply_id}')->group(function () {
        Route::get('/view', EditSupplyController::class)->name('view');
        Route::get('/edit', EditSupplyController::class)->name('edit');
        Route::get('/details', GetSupplyDetailsController::class)->name('details');

        Route::get('/register-products', RegisterProductsController::class)->name('register-products');
        Route::get('/scan-bottles', ScanBottlesController::class)->name('scan-bottles');

        Route::delete('/delete', DeleteSupplyController::class)->name('delete');
    });
});
