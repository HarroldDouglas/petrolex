<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Test\Delivery;

use App\Http\Controllers\Controller;
use App\Models\DeliveryTracking;
use Illuminate\Http\JsonResponse;

final class GetActiveDeliveriesController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $deliveries = DeliveryTracking::whereIn('status', ['pending', 'started', 'in_progress'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'deliveries' => $deliveries,
        ]);
    }
}
