<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TransactionReportController extends Controller
{
    /**
     * Display the transaction report.
     *
     * Route: GET /rapport-transaction
     * Name: transaction_report
     */
    public function __invoke(Request $request)
    {
        return view('dashboard.transaction-report');
    }
}
