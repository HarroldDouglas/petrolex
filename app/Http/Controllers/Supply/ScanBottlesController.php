<?php

namespace App\Http\Controllers\Supply;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ScanBottlesController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, $supply_id, $type_id)
    {
        // Dans un cas réel, vous récupéreriez ces données depuis la base de données
        $supplyData = [
            'id' => $supply_id,
            'title' => 'Approvisionnement du 14 Mai 2025',
            'date' => '2025-05-14 10:00:00',
            'product' => [
                'id' => $type_id,
                'type' => 'Bouteille de 12KG',
                'quantity' => 50,
                'scanned' => 0,
            ],
        ];

        return view('supplies.scan-bottles', [
            'supply' => $supplyData,
            'title' => $supplyData['title'],
            'date' => $supplyData['date'],
        ]);
    }
}
