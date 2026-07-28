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
 * Public install page for the Petrolex Manager Android app (Flutter).
 * Distributed direct (no Play Store): manager visits the URL, downloads the
 * APK, installs it, logs in with their normal Petrolex credentials.
 */
Route::get('/mobileapp', function () {
    $apkPath = storage_path('app/mobile-app/petrolex-manager.apk');
    $manifestPath = storage_path('app/mobile-app/manifest.json');
    $available = file_exists($apkPath);
    $manifest = $available && file_exists($manifestPath)
        ? json_decode(file_get_contents($manifestPath), true)
        : null;

    return view('mobile-app.install', [
        'available' => $available,
        'version' => $manifest['version'] ?? null,
        'buildDate' => $manifest['build_date'] ?? null,
        'sizeMb' => $available ? round(filesize($apkPath) / 1048576, 1) : null,
    ]);
})->name('mobile-app.install');

Route::get('/mobileapp/download', function () {
    $apkPath = storage_path('app/mobile-app/petrolex-manager.apk');
    abort_unless(file_exists($apkPath), 404, 'APK pas encore disponible.');

    return response()->download($apkPath, 'petrolex-manager.apk', [
        'Content-Type' => 'application/vnd.android.package-archive',
    ]);
})->name('mobile-app.download');

/**
 * Include auth routes (must be outside auth middleware)
 */
require __DIR__.'/web/auth.php';

/**
 * All authenticated routes
 */
Route::middleware(['web', 'auth', 'staff'])->group(function () {
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
