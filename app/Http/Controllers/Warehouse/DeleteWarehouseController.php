<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DeleteWarehouseController extends Controller
{
    /**
     * Remove the specified warehouse from storage.
     *
     * Route: DELETE /distribution-centers/{center_id}/delete
     * Name: distribution-centers.delete
     */
    public function __invoke(Request $request)
    {
        //
    }
}
