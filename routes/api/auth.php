<?php

use App\Http\Api\Controllers\Auth\CheckAuthController;
use App\Http\Api\Controllers\Auth\ForgotPasswordController;
use App\Http\Api\Controllers\Auth\GetProfileController;
use App\Http\Api\Controllers\Auth\LoginController;
use App\Http\Api\Controllers\Auth\LogoutController;
use App\Http\Api\Controllers\Auth\ResendOtpController;
use App\Http\Api\Controllers\Auth\VerifyOtpController;
use Illuminate\Support\Facades\Route;

Route::post('/login', LoginController::class)->name('api.login');
Route::post('/register/customer', \App\Http\Api\Controllers\Customer\StoreCustomerController::class)->name('api.register.customer');
Route::post('/verify-otp', VerifyOtpController::class)->name('api.verify-otp');
Route::post('/resend-otp', ResendOtpController::class)->name('api.resend-otp');
Route::post('/forgot-password', ForgotPasswordController::class)->name('api.forgot-password');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/check', CheckAuthController::class)->name('api.auth.check');
    Route::post('/logout', LogoutController::class)->name('api.logout');
    Route::get('/user', GetProfileController::class)->name('api.user');
    Route::patch('/profile', \App\Http\Api\Controllers\UpdateProfileController::class)->name('api.profile.update');
    Route::patch('/password', \App\Http\Api\Controllers\Auth\UpdatePasswordController::class)->name('api.password.update');
});
