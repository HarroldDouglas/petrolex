<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GetDistributionCentersController extends Controller
{
    /**
     * Display a listing of the distribution centers.
     *
     * Route: GET /distribution-centers
     * Name: distribution-centers.list
     */
    public function __invoke(Request $request)
    {
        return view('distribution-center.index');
    }
}
