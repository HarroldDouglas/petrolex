<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Test\Delivery;

use App\Http\Controllers\Controller;
use App\Models\DeliveryTracking;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

final class CreateDeliveryTrackingController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        /** @var array<string, mixed> $validated */
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'driver_name' => 'required|string|max:255',
            'driver_phone' => 'required|string|max:255',
            'destination_lat' => 'required|numeric',
            'destination_lng' => 'required|numeric',
            'destination_address' => 'required|string',
        ]);

        $delivery = DeliveryTracking::create([
            'order_number' => 'ORD-' . strtoupper(uniqid()),
            'customer_name' => $validated['customer_name'],
            'driver_name' => $validated['driver_name'],
            'driver_phone' => $validated['driver_phone'],
            'destination_lat' => $validated['destination_lat'],
            'destination_lng' => $validated['destination_lng'],
            'destination_address' => $validated['destination_address'],
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'delivery' => $delivery,
        ], 201);
    }
}
