<?php

namespace App\Http\Controllers\Supply;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GetSupplyDetailsController extends Controller
{
    /**
     * Display the specified supply details.
     *
     * Route: GET /supplies/{supply_id}/details
     * Name: supplies.details
     */
    public function __invoke(Request $request)
    {
        return view('supplies.supply-details');
    }
}
