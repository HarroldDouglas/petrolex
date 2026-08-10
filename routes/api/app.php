<?php

use App\Http\Api\Controllers\App\GetAdvertisingBannersController;
use App\Http\Api\Controllers\App\GetAppVersionController;
use App\Http\Api\Controllers\App\GetPrivacyPolicyController;
use App\Http\Api\Controllers\App\GetSupportContactController;
use App\Http\Api\Controllers\App\GetTermsAndConditionsController;
use App\Http\Api\Controllers\App\PreviewPrivacyPolicyController;
use App\Http\Api\Controllers\App\PreviewTermsAndConditionsController;
use App\Http\Api\Controllers\App\StoreMobileAppLogController;
use App\Http\Api\Controllers\App\UpdateAppVersionController;
use Illuminate\Support\Facades\Route;

Route::prefix('app')->name('api.app.')->group(function () {
    Route::get('/terms-and-conditions', GetTermsAndConditionsController::class)->name('terms-and-conditions');
    Route::get('/terms-and-conditions/preview', PreviewTermsAndConditionsController::class)->name('terms-and-conditions.preview');
    Route::get('/privacy-policy', GetPrivacyPolicyController::class)->name('privacy-policy');
    Route::get('/privacy-policy/preview', PreviewPrivacyPolicyController::class)->name('privacy-policy.preview');
    Route::get('/support/contact', GetSupportContactController::class)->name('support.contact');
    Route::get('/advertising/banners', GetAdvertisingBannersController::class)->name('advertising.banners');
    Route::get('/version', GetAppVersionController::class)->name('version');

    // Unauthenticated on purpose: crashes often happen before login. Throttled against abuse.
    Route::post('/logs', StoreMobileAppLogController::class)
        ->middleware('throttle:30,1')
        ->name('logs.store');

    // Réservé au dev mobile — protégé par X-Mobile-Dev-Token
    Route::middleware('mobile.dev')->group(function () {
        Route::put('/version/{app_type}', UpdateAppVersionController::class)->name('version.update');
    });
});
