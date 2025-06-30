<?php

namespace App\Http\Controllers\Supply;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GetSupplyListController extends Controller
{
    /**
     * Display a listing of the supplies.
     *
     * Route: GET /supplies
     * Name: supplies.list
     */
    public function __invoke(Request $request)
    {
        return view('supplies.index');
    }
}
