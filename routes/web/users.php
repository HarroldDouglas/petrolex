<?php

/**
 * Routes for the user routes management
 *
 * @author  "Afrik Solutions" <contact@afrik-solutions.com>
 * @license MIT License
 */

use App\Http\Controllers\User\CreateUserController;
use App\Http\Controllers\User\DeleteUserController;
use App\Http\Controllers\User\EditUserController;
use App\Http\Controllers\User\GetUserDataTableController;
use App\Http\Controllers\User\GetUserDetailsController;
use App\Http\Controllers\User\GetUsersController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'users', 'as' => 'users.'], function () {
    Route::get('/', GetUsersController::class)->name('list');
    Route::get('/create', CreateUserController::class)->name('create');
    Route::get('/{user_id}/edit', EditUserController::class)->name('edit');
    Route::get('/{user_id}/details', GetUserDetailsController::class)->name('details');
    Route::delete('/{user_id}/delete', DeleteUserController::class)->name('delete');
    Route::get('/json', GetUserDataTableController::class)->name('json');
});
