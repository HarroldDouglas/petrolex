<?php

/**
 * Routes for the user routes management
 *
 * @author  "Afrik Solutions" <contact@afrik-solutions.com>
 * @license MIT License
 */

use App\Http\Controllers\User\CreateUserController;
use App\Http\Controllers\User\Customer\GetCustomerDetailsController;
use App\Http\Controllers\User\DeleteUserController;
use App\Http\Controllers\User\DeliveryPerson\GetDeliveryPersonDetailsController;
use App\Http\Controllers\User\EditUserController;
use App\Http\Controllers\User\GetUserDataTableController;
use App\Http\Controllers\User\GetUserDetailsController;
use App\Http\Controllers\User\GetUsersController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'users', 'as' => 'users.'], function () {
    Route::get('/', GetUsersController::class)->name('list');
    Route::get('/create', CreateUserController::class)->name('create');
    Route::get('/json', GetUserDataTableController::class)->name('json');

    Route::group(['prefix' => '{user_id}'], function () {
        Route::get('edit', EditUserController::class)->name('edit');
        Route::get('details', GetUserDetailsController::class)->name('details');
        Route::get('delivery/details', GetDeliveryPersonDetailsController::class)->name('delivery.details');
        Route::get('customer/details', GetCustomerDetailsController::class)->name('customer.details');
        Route::delete('delete', DeleteUserController::class)->name('delete');
    });
});
