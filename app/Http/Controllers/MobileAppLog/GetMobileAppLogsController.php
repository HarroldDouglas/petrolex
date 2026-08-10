<?php

namespace App\Http\Controllers\MobileAppLog;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class GetMobileAppLogsController extends Controller
{
    /**
     * Display the mobile app logs list page.
     *
     * Route: GET /mobile-app-logs
     * Name: mobile-app-logs.index
     */
    public function __invoke(): View
    {
        return view('mobile-app-logs.index');
    }
}
