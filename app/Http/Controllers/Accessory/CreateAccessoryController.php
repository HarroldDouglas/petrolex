<?php

namespace App\Http\Controllers\Accessory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CreateAccessoryController extends Controller
{
    /**
     * Show the form for creating a new accessory.
     *
     * Route: GET /accessories/create
     * Name: accessories.create
     */
    public function __invoke(Request $request)
    {
        return view('accessories.create');
    }
}
