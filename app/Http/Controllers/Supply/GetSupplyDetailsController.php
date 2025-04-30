<?php

namespace App\Http\Controllers\Supply;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GetSupplyDetailsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        return view('supplies.supply-details');
    }
}
