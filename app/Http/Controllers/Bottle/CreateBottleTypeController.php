<?php

namespace App\Http\Controllers\Bottle;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CreateBottleTypeController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        return view('bottle-types.create-bottle-type');
    }
}
