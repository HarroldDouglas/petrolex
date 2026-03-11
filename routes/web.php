<?php

use App\Http\Controllers\AuthCheckController;
use App\Http\Controllers\PrivacyPolicyController;
use Illuminate\Support\Facades\Route;

Route::get('/', AuthCheckController::class);
Route::get('/privacy-policy', PrivacyPolicyController::class)->name('privacy-policy');

/**
 * Public routes for Google Play Store compliance
 * Account deletion information pages
 */
Route::get('/account-deletion/customer', function () {
    return view('account-deletion.customer');
})->name('account-deletion.customer');

Route::get('/account-deletion/delivery', function () {
    return view('account-deletion.delivery');
})->name('account-deletion.delivery');

/**
 * Include auth routes (must be outside auth middleware)
 */
require __DIR__.'/web/auth.php';

/**
 * All authenticated routes
 */
Route::middleware(['web', 'auth'])->group(function () {
    require __DIR__.'/web/dashboard.php';
    require __DIR__.'/web/admin.php';
    require __DIR__.'/web/orders.php';
    require __DIR__.'/web/users.php';
    require __DIR__.'/web/bottles.php';
    require __DIR__.'/web/accessories.php';
    require __DIR__.'/web/products.php';
    require __DIR__.'/web/deliveries.php';
    require __DIR__.'/web/reports.php';
    require __DIR__.'/web/settings.php';
    require __DIR__.'/web/distribution-centers.php';
    require __DIR__.'/web/supplies.php';
    require __DIR__.'/web/orders.php';
    require __DIR__.'/web/municipalities.php';
    require __DIR__.'/web/neighborhoods.php';
    require __DIR__.'/web/roles.php';
});

if (! app()->isProduction()) {
    Route::get('/test/manager/dashboard', function () {
        return view('manager.dashboard');
    })->name('manager.dashboard');
}
