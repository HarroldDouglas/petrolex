<?php

namespace App\Http\Controllers\Bottle;

use App\Http\Controllers\Controller;
use App\Models\BottleType;
use Illuminate\Http\Request;

class EditBottleTypeController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(BottleType $bottleType)
    {
        return view('bottles.edit-bottle-type',
            compact('bottleType')
        );
    }
}
