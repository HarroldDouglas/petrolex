<?php

namespace App\Http\Controllers\Bottle;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CreateBottleTypeController extends Controller
{
    /**
     * Show the form for creating a new bottle type.
     *
     * Route: GET /bottles/types/create
     * Name: bottles.types.create
     */
    public function __invoke(Request $request)
    {
        return view('bottles.create-bottle-type');
    }
}
