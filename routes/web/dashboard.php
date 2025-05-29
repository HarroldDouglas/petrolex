<?php

use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\StockMovementController;
use App\Http\Controllers\Dashboard\TransactionReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/stock-movement', StockMovementController::class)->name('stock_movement');
    Route::get('/rapport-transaction', TransactionReportController::class)->name('transaction_report');
});
