<?php

use App\Http\Api\Controllers\App\GetPrivacyPolicyController;
use App\Http\Api\Controllers\App\GetSupportContactController;
use App\Http\Api\Controllers\App\GetTermsAndConditionsController;
use App\Http\Api\Controllers\App\PreviewPrivacyPolicyController;
use App\Http\Api\Controllers\App\PreviewTermsAndConditionsController;
use Illuminate\Support\Facades\Route;

Route::prefix('app')->name('api.app.')->group(function () {
    Route::get('/terms-and-conditions', GetTermsAndConditionsController::class)->name('terms-and-conditions');
    Route::get('/terms-and-conditions/preview', PreviewTermsAndConditionsController::class)->name('terms-and-conditions.preview');
    Route::get('/privacy-policy', GetPrivacyPolicyController::class)->name('privacy-policy');
    Route::get('/privacy-policy/preview', PreviewPrivacyPolicyController::class)->name('privacy-policy.preview');
    Route::get('/support/contact', GetSupportContactController::class)->name('support.contact');
});
