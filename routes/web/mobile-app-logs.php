<?php

use App\Http\Controllers\MobileAppLog\GetMobileAppLogsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::prefix('mobile-app-logs')->name('mobile-app-logs.')->group(function () {
        Route::get('/', GetMobileAppLogsController::class)->name('index');
    });
});
