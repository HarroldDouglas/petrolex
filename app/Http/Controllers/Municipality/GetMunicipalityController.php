<?php

namespace App\Http\Controllers\Municipality;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GetMunicipalityController extends Controller
{
    /**
     * Display a listing of municipalities.
     *
     * Route: GET /municipalities
     * Name: municipalities.index
     */
    public function __invoke(Request $request)
    {
        return view('municipalities.index');
    }
}
