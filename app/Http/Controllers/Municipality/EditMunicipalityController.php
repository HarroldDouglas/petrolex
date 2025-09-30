<?php

namespace App\Http\Controllers\Municipality;

use App\Http\Controllers\Controller;
use App\Models\Geography\Municipality;
use App\Services\Geography\MunicipalityService;
use Illuminate\Http\Request;

class EditMunicipalityController extends Controller
{
    public function __construct(private MunicipalityService $municipalityService) {}

    /**
     * Show the form for editing the specified municipality.
     *
     * Route: GET /municipalities/{municipality}/edit
     * Name: municipalities.edit
     */
    public function __invoke(Request $request, Municipality $municipality)
    {
        return view('municipalities.edit', compact('municipality'));
    }
}
