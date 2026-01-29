<?php

namespace App\Http\Controllers\Neighborhood;

use App\Http\Controllers\Controller;
use App\Models\Geography\Neighborhood;
use Illuminate\View\View;

class EditNeighborhoodController extends Controller
{
    /**
     * Display the edit neighborhood form.
     *
     * Route: GET /neighborhoods/edit/{neighborhood}
     * Name: neighborhoods.edit
     */
    public function __invoke(Neighborhood $neighborhood): View
    {
        return view('neighborhoods.edit', compact('neighborhood'));
    }
}
