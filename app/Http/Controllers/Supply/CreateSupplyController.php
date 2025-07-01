<?php

namespace App\Http\Controllers\Supply;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CreateSupplyController extends Controller
{
    /**
     * Show the form for creating a new supply.
     *
     * Route: GET /supplies/create
     * Name: supplies.create
     */
    public function __invoke(Request $request)
    {
        return view('supplies.create');
    }
}
