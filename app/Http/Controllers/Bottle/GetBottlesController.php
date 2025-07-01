<?php

namespace App\Http\Controllers\Bottle;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GetBottlesController extends Controller
{
    /**
     * Display a listing of the bottles.
     *
     * Route: GET /bottles
     * Name: bottles.index
     */
    public function __invoke(Request $request)
    {
        return view('bottles.index');
    }
}
