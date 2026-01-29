<?php

namespace App\Http\Controllers\Neighborhood;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class GetNeighborhoodsController extends Controller
{
    /**
     * Display the neighborhoods list page.
     *
     * Route: GET /neighborhoods
     * Name: neighborhoods.index
     */
    public function __invoke(): View
    {
        return view('neighborhoods.index');
    }
}
