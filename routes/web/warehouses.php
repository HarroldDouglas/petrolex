<?php

/**
 * Routes for the warehouse routes management
 *
 * @author  "Afrik Solutions" <contact@afrik-solutions.com>
 * @license MIT License
 */

use App\Http\Controllers\Warehouse\CreateWarehouseController;
use App\Http\Controllers\Warehouse\DeleteWarehouseController;
use App\Http\Controllers\Warehouse\EditWarehouseController;
use App\Http\Controllers\Warehouse\GetWarehouseDataTableController;
use App\Http\Controllers\Warehouse\GetWarehouseDetailsController;
use App\Http\Controllers\Warehouse\GetWarehouseListController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'warehouses', 'as' => 'warehouses.'], function () {
    Route::get('/', GetWarehouseListController::class)->name('list');
    Route::get('/create', CreateWarehouseController::class)->name('create');
    Route::get('/{warehouse_id}/edit', EditWarehouseController::class)->name('edit');
    Route::get('/{warehouse_id}/details', GetWarehouseDetailsController::class)->name('details');
    Route::delete('/{warehouse_id}/delete', DeleteWarehouseController::class)->name('delete');
    Route::get('/json', GetWarehouseDataTableController::class)->name('json');
});
