<?php

/**
 * Routes for the Role management
 *
 * @author  "Afrik Solutions" <contact@afrik-solutions.com>
 * @license MIT License
 */

use App\Http\Controllers\Role\CreateRoleController;
use App\Http\Controllers\Role\EditRoleController;
use App\Http\Controllers\Role\GetRoleListController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'roles', 'as' => 'roles.'], function () {
    Route::get('/', GetRoleListController::class)->name('list');
    Route::get('/create', CreateRoleController::class)->name('create');
    Route::get('/{role}/edit', EditRoleController::class)->name('edit');
});