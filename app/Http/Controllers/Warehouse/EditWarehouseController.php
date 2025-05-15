<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EditWarehouseController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, $warehouse_id)
    {
        // Mock data for demonstration
        $warehouse = [
            'id' => $warehouse_id,
            'name' => 'Point '.chr(64 + $warehouse_id),
            'city' => $warehouse_id % 2 ? 'Yaoundé' : 'Douala',
            'address' => 'Address for warehouse '.$warehouse_id,
            'phone' => '+237 6'.str_pad($warehouse_id, 8, '9'),
            'email' => 'warehouse'.$warehouse_id.'@example.com',
            'postal_code' => str_pad($warehouse_id * 100, 5, '0', STR_PAD_LEFT),
            'latitude' => 3.8667 + ($warehouse_id / 100),
            'longitude' => 11.5167 + ($warehouse_id / 100),
            'storage_capacity' => $warehouse_id * 100,
            'status' => $warehouse_id % 2 ? 'ACTIF' : 'INACTIF',
            'created_at' => date('Y-m-d H:i:s', strtotime("-$warehouse_id days")),
        ];

        return view('warehouse.edit-warehouse', compact('warehouse'));
    }
}
