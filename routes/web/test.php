<?php

use App\Http\Controllers\Test\TestUserPermissionsController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'test'], function () {
    Route::get('/user/{user}/permissions', [TestUserPermissionsController::class, 'testUserPermissions']);
    Route::post('/user/{user}/permissions/{permission}/toggle', [TestUserPermissionsController::class, 'togglePermission']);
});
