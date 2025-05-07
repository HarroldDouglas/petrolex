<?php

use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('bottles/list', function () {})->name('bottles.list');
});
