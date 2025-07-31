<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Test\Delivery;

use App\Http\Controllers\Controller;
use App\Models\DeliveryTracking;
use Illuminate\Http\JsonResponse;

final class GetDeliveryTrackingDetailsController extends Controller
{
    public function __invoke(string $orderNumber): JsonResponse
    {
        $delivery = DeliveryTracking::where('order_number', $orderNumber)->firstOrFail();

        return response()->json([
            'success' => true,
            'delivery' => $delivery,
        ]);
    }
}
