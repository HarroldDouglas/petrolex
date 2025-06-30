<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CreateDistributionCenterController extends Controller
{
    /**
     * Show the form for creating a new distribution center.
     *
     * Route: GET /distribution-centers/create
     * Name: distribution-centers.create
     */
    public function __invoke(Request $request)
    {
        return view('distribution-center.create');
    }
}
