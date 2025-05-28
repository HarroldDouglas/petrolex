<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class StockMovementController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        // Check permission
        $this->middleware(['permission:reports.manage']);

        // Get filtering parameters
        $period = $request->input('period', 'week');
        $startDate = null;
        $endDate = null;

        // Set date range based on period
        if ($period === 'custom' && $request->has('start_date') && $request->has('end_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();
        } else {
            switch ($period) {
                case 'today':
                    $startDate = Carbon::today();
                    $endDate = Carbon::today()->endOfDay();
                    break;
                case 'yesterday':
                    $startDate = Carbon::yesterday();
                    $endDate = Carbon::yesterday()->endOfDay();
                    break;
                case 'week':
                    $startDate = Carbon::now()->startOfWeek();
                    $endDate = Carbon::now()->endOfWeek();
                    break;
                case 'month':
                    $startDate = Carbon::now()->startOfMonth();
                    $endDate = Carbon::now()->endOfMonth();
                    break;
                case 'year':
                    $startDate = Carbon::now()->startOfYear();
                    $endDate = Carbon::now()->endOfYear();
                    break;
                default:
                    $startDate = Carbon::now()->subDays(7);
                    $endDate = Carbon::now();
            }
        }

        // Mock data for statistics (to be replaced with actual data from models)
        $stats = [
            'total_entries' => 4500,
            'total_exits' => 2000,
            'total_exchanges' => 850,
            'current_stock' => 3350,
        ];

        // Mock movement data (to be replaced with actual data from models)
        $movements = [
            [
                'date' => '22/04/2025 - 10:14',
                'type' => 'Entrée',
                'type_class' => 'primary',
                'products' => '100 x Bouteille de 9kg',
                'destination' => 'Point YDE A',
                'user' => 'Ntang Luc',
            ],
            [
                'date' => '22/04/2025 - 14:45',
                'type' => 'Échange',
                'type_class' => 'success',
                'products' => '30 x Bouteille de 12kg',
                'destination' => 'Point DLA B',
                'user' => 'Kamga Lionel',
            ],
            [
                'date' => '22/04/2025 - 17:15',
                'type' => 'Sortie',
                'type_class' => 'warning',
                'products' => 'Bouteille 12kg',
                'destination' => 'Client final',
                'user' => 'Ekani Paul',
            ],
            [
                'date' => '22/04/2025 - 18:30',
                'type' => 'Échange',
                'type_class' => 'success',
                'products' => 'Bouteille 9kg',
                'destination' => 'Point YDE A',
                'user' => 'Fouda Mireille',
            ],
        ];

        return view('dashboard.stock-movement', [
            'period' => $period,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'stats' => $stats,
            'movements' => $movements,
        ]);
    }
}
