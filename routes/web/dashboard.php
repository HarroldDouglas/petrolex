<?php

use App\Http\Controllers\Dashboard\StockSaleDashboardController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\StockMovementController;
use App\Http\Controllers\Dashboard\FinancialReportController;
use App\Http\Controllers\Dashboard\OrderReportController;
use App\Http\Controllers\Dashboard\TransactionReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/tableau-de-bord', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/performance-point-de-distribution', StockSaleDashboardController::class)->name('warehouse_stock_dashboard');
    Route::get('/mouvement-de-stock', StockMovementController::class)->name('stock_movement');
    Route::get('/rapport-financier', FinancialReportController::class)->name('financial_report');
    Route::get('/rapport-commande', OrderReportController::class)->name('order_report');
    Route::get('/rapport-transaction', TransactionReportController::class)->name('transaction_report');
});
