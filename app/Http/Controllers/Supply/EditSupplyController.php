<?php

namespace App\Http\Controllers\Supply;

use App\Http\Controllers\Controller;
use App\Services\SupplierDeliveryService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EditSupplyController extends Controller
{
    protected $supplierDeliveryService;

    public function __construct(SupplierDeliveryService $supplierDeliveryService)
    {
        $this->supplierDeliveryService = $supplierDeliveryService;
    }

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
        $supply = $this->supplierDeliveryService->find($supplyId);

        return view('supplies.edit', compact('supply'));
    }
}
