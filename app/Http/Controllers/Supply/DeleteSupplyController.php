<?php

namespace App\Http\Controllers\Supply;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DeleteSupplyController extends Controller
{
    /**
     * Remove the specified supply from storage.
     *
     * Route: DELETE /supplies/{supply_id}/delete
     * Name: supplies.delete
     */
    public function __invoke(Request $request)
    {
        //
    }
}
