<?php

use App\Http\Api\Controllers\Auth\GetProfileController;
use App\Http\Api\Controllers\Auth\LoginController;
use App\Http\Api\Controllers\Auth\LogoutController;
use Illuminate\Support\Facades\Route;

Route::post('/login', LoginController::class)->name('api.login');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', LogoutController::class)->name('api.logout');
    Route::get('/user', GetProfileController::class)->name('api.user');
});
