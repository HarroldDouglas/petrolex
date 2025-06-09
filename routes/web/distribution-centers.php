<?php

/**
 * Routes for the warehouse routes management
 *
 * @author  "Afrik Solutions" <contact@afrik-solutions.com>
 * @license MIT License
 */

use App\Http\Controllers\Warehouse\CreateDistributionCenterController;
use App\Http\Controllers\Warehouse\DeleteWarehouseController;
use App\Http\Controllers\Warehouse\EditDistributionCenterController;
use App\Http\Controllers\Warehouse\GetDistributionCenterDetailsController;
use App\Http\Controllers\Warehouse\GetDistributionCentersController;
use App\Http\Controllers\Warehouse\GetWarehouseDataTableController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'distribution-centers', 'as' => 'distribution-centers.'], function () {
    Route::get('/', GetDistributionCentersController::class)->name('list');
    Route::get('/create', CreateDistributionCenterController::class)->name('create');
    Route::get('/{center_id}/edit', EditDistributionCenterController::class)->name('edit');
    Route::get('/{center_id}/details', GetDistributionCenterDetailsController::class)->name('details');
    Route::delete('/{center_id}/delete', DeleteWarehouseController::class)->name('delete');
    Route::get('/json', GetWarehouseDataTableController::class)->name('json');
});
