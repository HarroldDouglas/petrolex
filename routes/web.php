<?php

use App\Http\Controllers\AuthCheckController;
use Illuminate\Support\Facades\Route;

Route::get('/', AuthCheckController::class);

/**
 * Include routes modules
 */
require __DIR__.'/web/auth.php';
require __DIR__.'/web/dashboard.php';

require __DIR__.'/web/admin.php';
require __DIR__.'/web/orders.php';
require __DIR__.'/web/users.php';
require __DIR__.'/web/bottles.php';
require __DIR__.'/web/products.php';
require __DIR__.'/web/deliveries.php';
require __DIR__.'/web/reports.php';
require __DIR__.'/web/settings.php';

require __DIR__.'/web/warehouses.php';
require __DIR__.'/web/supplies.php';
require __DIR__.'/web/orders.php';
