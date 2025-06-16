<?php

namespace App\Http\Controllers\Accessory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CreateAccessoryController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        return view('accessories.create');
    }
}
