<?php

/**
 * Routes for the user routes management
 *
 * @author  "Afrik Solutions" <contact@afrik-solutions.com>
 * @license MIT License
 */

use App\Http\Controllers\User\CreateUserController;
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
    Route::get('/{user_id}/edit', EditUserController::class)->name('edit');
    Route::get('/{user_id}/details', GetUserDetailsController::class)->name('details');
    Route::get('/{user}/delivery/details', GetDeliveryPersonDetailsController::class)->name('delivery.details');
    Route::delete('/{user_id}/delete', DeleteUserController::class)->name('delete');
    Route::get('/toggle-status', function () {
        // TODO: implement this in a seperate controller
        session()->flash('success', 'Le statut de lutilisateur a été mise à jour');

        return redirect()->back();
    })->name('toggle-status');
    Route::get('/destroy', function () {
        // ToDO: implement this in a seperate controller
        session()->flash('success', 'Lutilisateur a bien été supprimé');

        return redirect()->back();
    })->name('destroy');
    Route::get('/json', GetUserDataTableController::class)->name('json');
});
