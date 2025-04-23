<?php

use App\Http\Controllers\Dashboard\StockSaleDashboardController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\StockMovementController;
use App\Http\Controllers\Dashboard\FinancialReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/tableau-de-bord', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/performance-point-de-distribution', StockSaleDashboardController::class)->name('warehouse-stock-dashboard');
    Route::get('/mouvement-de-stock', StockMovementController::class)->name('stock-movement');
    Route::get('/rapport-financier', FinancialReportController::class)->name('financial-report');
});
