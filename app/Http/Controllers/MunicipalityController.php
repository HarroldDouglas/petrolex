<?php

namespace App\Http\Controllers;

use App\Models\Geography\Municipality;
use Illuminate\Contracts\View\View;

class MunicipalityController extends Controller
{
    public function index(): View
    {
        return view('municipalities.index');
    }

    public function create(): View
    {
        return view('municipalities.create');
    }

    public function edit(Municipality $municipality): View
    {
        return view('municipalities.edit', compact('municipality'));
    }
}
