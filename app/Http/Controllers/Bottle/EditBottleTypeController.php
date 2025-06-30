<?php

namespace App\Http\Controllers\Bottle;

use App\Http\Controllers\Controller;
use App\Models\BottleType;

class EditBottleTypeController extends Controller
{
    /**
     * Show the form for editing the specified bottle type.
     *
     * Route: GET /bottles/types/edit/{bottleType}
     * Name: bottles.types.edit
     */
    public function __invoke(BottleType $bottleType)
    {
        return view('bottles.edit-bottle-type',
            compact('bottleType')
        );
    }
}
