<?php

namespace App\Http\Controllers\Supply;

use App\Http\Controllers\Controller;
use App\Models\SupplierDelivery;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EditSupplyController extends Controller
{
    /**
     * Show the form for editing the specified supply.
     *
     * Route: GET /supplies/{supply_id}/edit
     * Name: supplies.edit
     *
     * Route: GET /supplies/{supply_id}/view
     * Name: supplies.view
     */
    public function __invoke(Request $request, int $supplyId): View
    {
        // TODO: replace with a service
        $supply = SupplierDelivery::findOrFail($supplyId);

        return view('supplies.edit', compact('supply'));
    }
}
