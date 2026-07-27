<?php

use App\Http\Api\Controllers\Auth\CheckAuthController;
use App\Http\Api\Controllers\Auth\ForgotPasswordController;
use App\Http\Api\Controllers\Auth\GetProfileController;
use App\Http\Api\Controllers\Auth\LoginController;
use App\Http\Api\Controllers\Auth\LoginCustomerController;
use App\Http\Api\Controllers\Auth\LoginDeliveryController;
use App\Http\Api\Controllers\Auth\LogoutController;
use App\Http\Api\Controllers\Auth\ResendOtpController;
use App\Http\Api\Controllers\Auth\VerifyOtpController;
use Illuminate\Support\Facades\Route;

// Rate-limited public auth endpoints: guard against password brute-force,
// OTP brute-force (6-digit code) and SMS/OTP flooding (Twilio = real cost).
Route::middleware('throttle:8,1')->group(function () {
    // New secure login endpoints - USE THESE!
    Route::post('/login/customer', LoginCustomerController::class)->name('api.login.customer');
    Route::post('/login/delivery', LoginDeliveryController::class)->name('api.login.delivery');

    // Old login endpoint - DEPRECATED, will be removed soon
    Route::post('/login', LoginController::class)->name('api.login');

    Route::post('/verify-otp', VerifyOtpController::class)->name('api.verify-otp');
    Route::post('/forgot-password', ForgotPasswordController::class)->name('api.forgot-password');
});

// Sending an SMS/OTP is the most expensive + abusable action: throttle harder.
Route::middleware('throttle:4,1')->group(function () {
    Route::post('/register/customer', \App\Http\Api\Controllers\Customer\StoreCustomerController::class)->name('api.register.customer');
    Route::post('/resend-otp', ResendOtpController::class)->name('api.resend-otp');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/check', CheckAuthController::class)->name('api.auth.check');
    Route::post('/logout', LogoutController::class)->name('api.logout');
    Route::get('/user', GetProfileController::class)->name('api.user');
    Route::patch('/profile', \App\Http\Api\Controllers\UpdateProfileController::class)->name('api.profile.update');
    Route::patch('/password', \App\Http\Api\Controllers\Auth\UpdatePasswordController::class)->name('api.password.update');
});
