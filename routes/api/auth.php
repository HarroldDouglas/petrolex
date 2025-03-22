<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

// API Auth Routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('auth:sanctum');
