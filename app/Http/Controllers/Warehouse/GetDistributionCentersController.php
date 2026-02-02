<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GetDistributionCentersController extends Controller
{
    /**
     * Display a listing of the distribution centers.
     *
     * Route: GET /distribution-centers
     * Name: distribution-centers.list
     */
    public function __invoke(Request $request)
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();

        // Si l'utilisateur est un responsable de centre, rediriger vers SON centre
        if ($user && $user->hasRole('center_manager')) {
            $centerIds = $user->distributionCenters()->pluck('distribution_center_id')->toArray();

            if (! empty($centerIds)) {
                // Rediriger vers le détail du premier centre assigné
                return redirect()->route('distribution-centers.details', $centerIds[0]);
            }
        }

        return view('distribution-center.index');
    }
}
