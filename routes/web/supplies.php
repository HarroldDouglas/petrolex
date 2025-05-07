<?php

use App\Http\Controllers\Supply\CreateSupplyController;
use App\Http\Controllers\Supply\DeleteSupplyController;
use App\Http\Controllers\Supply\EditSupplyController;
use App\Http\Controllers\Supply\GetSupplyDetailsController;
use App\Http\Controllers\Supply\GetSupplyListController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'supplies', 'as' => 'supplies.'], function () {
    Route::get('/', GetSupplyListController::class)->name('list');
    Route::get('/create', CreateSupplyController::class)->name('create');
    Route::get('/{supply_id}/edit', EditSupplyController::class)->name('edit');
    Route::get('/{supply_id}/details', GetSupplyDetailsController::class)->name('details');
    Route::delete('/{supply_id}/delete', DeleteSupplyController::class)->name('delete');
});
