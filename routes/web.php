<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index');
});

Route::view('index', 'index')->name('index');
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
});

Route::get('/test-dynamic-datatable', function () {
    return view('test-datatable');
})->name('test.dynamic.datatable');

Route::view('profile', 'profile')->name('profile');
/**
 * Include routes modules
 */
require __DIR__.'/web/auth.php';
require __DIR__.'/web/dashboard.php';

require __DIR__.'/web/admin.php';
require __DIR__.'/web/orders.php';
require __DIR__.'/web/users.php';
require __DIR__.'/web/bottles.php';
require __DIR__.'/web/deliveries.php';
require __DIR__.'/web/reports.php';
require __DIR__.'/web/settings.php';

require __DIR__.'/web/warehouses.php';
require __DIR__.'/web/supplies.php';
require __DIR__.'/web/orders.php';
