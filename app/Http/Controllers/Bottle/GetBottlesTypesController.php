<?php

namespace App\Http\Controllers\Bottle;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GetBottlesTypesController extends Controller
{
    /**
     * Display a listing of the bottle types.
     *
     * Route: GET /bottles/types
     * Name: bottles.types.index
     */
    public function __invoke(Request $request)
    {
        return view('bottles.bottle-type-list');
    }
}
