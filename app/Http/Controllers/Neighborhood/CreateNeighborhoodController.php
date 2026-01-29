<?php

namespace App\Http\Controllers\Neighborhood;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class CreateNeighborhoodController extends Controller
{
    /**
     * Display the create neighborhood form.
     *
     * Route: GET /neighborhoods/create
     * Name: neighborhoods.create
     */
    public function __invoke(): View
    {
        return view('neighborhoods.create');
    }
}
