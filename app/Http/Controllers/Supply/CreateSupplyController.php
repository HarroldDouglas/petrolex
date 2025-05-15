<?php

namespace App\Http\Controllers\Supply;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CreateSupplyController extends Controller
{
    /**
     * Handle the incoming request to display the create form.
     */
    public function __invoke(Request $request)
    {
        return view('supplies.supply-create');
    }

    /**
     * Process the submitted form.
     */
    public function store(Request $request)
    {
        return redirect()->route('supplies.details', ['supply_id' => 1])
            ->with('success', 'Approvisionnement créé avec succès!');
    }
}
