<?php

namespace App\Http\Controllers\Municipality;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CreateMunicipalityController extends Controller
{
    /**
     * Show the form for creating a new municipality.
     *
     * Route: GET /municipalities/create
     * Name: municipalities.create
     */
    public function __invoke(Request $request)
    {
        return view('municipalities.create');
    }
}
