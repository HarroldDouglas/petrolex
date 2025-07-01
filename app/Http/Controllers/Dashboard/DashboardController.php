<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * Route: GET /dashboard
     * Name: dashboard
     */
    public function __invoke()
    {
        return view('dashboard.index');
    }
}
