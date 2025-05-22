<?php

use App\Http\Controllers\Supply\CreateSupplyController;
use App\Http\Controllers\Supply\DeleteSupplyController;
use App\Http\Controllers\Supply\EditSupplyController;
use App\Http\Controllers\Supply\GetSupplyDetailsController;
use App\Http\Controllers\Supply\GetSupplyListController;
use App\Http\Controllers\Supply\ScanBottlesController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->name('supplies.')->prefix('supplies')->
group(function () {
    Route::get('/', GetSupplyListController::class)->name('list');
    Route::get('/create', CreateSupplyController::class)->name('create');
    Route::post('/store', [CreateSupplyController::class, 'store'])->name('store');
    Route::get('/{supply_id}/edit', EditSupplyController::class)->name('edit');
    Route::get('/{supply_id}/details', GetSupplyDetailsController::class)->name('details');
    Route::get('/{supply_id}/bottle-type/{type_id}/scan', ScanBottlesController::class)->name('scan-bottles');
    Route::delete('/{supply_id}/delete', DeleteSupplyController::class)->name('delete');
});
