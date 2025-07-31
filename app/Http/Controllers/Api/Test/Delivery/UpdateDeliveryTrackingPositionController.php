<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Test\Delivery;

use App\Contracts\DeliveryTrackingServiceInterface;
use App\Http\Controllers\Controller;
use App\Models\DeliveryTracking;
use App\Events\DeliveryPositionUpdated;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

final class UpdateDeliveryTrackingPositionController extends Controller
{
    public function __construct(private readonly DeliveryTrackingServiceInterface $deliveryTrackingService)
    {
    }

    public function __invoke(Request $request, string $orderNumber): JsonResponse
    {
        /** @var array<string, mixed> $validated */
        $validated = $request->validate([
            'driver_lat' => 'required|numeric',
            'driver_lng' => 'required|numeric',
        ]);

        $delivery = DeliveryTracking::where('order_number', $orderNumber)->firstOrFail();

        $driverLng = filter_var($validated['driver_lng'], FILTER_VALIDATE_FLOAT);
        $driverLat = filter_var($validated['driver_lat'], FILTER_VALIDATE_FLOAT);

        if ($driverLng === false || $driverLat === false) {
            return response()->json(['success' => false, 'message' => 'Invalid driver coordinates.'], 422);
        }

        $routeData = $this->deliveryTrackingService->calculateRoute(
            $driverLng,
            $driverLat,
            (float) $delivery->destination_lng,
            (float) $delivery->destination_lat
        );

        $delivery->update([
            'driver_lat' => $driverLat,
            'driver_lng' => $driverLng,
            'estimated_duration' => $routeData->duration,
            'distance_remaining' => $routeData->distance,
            'status' => 'in_progress',
        ]);

        broadcast(new DeliveryPositionUpdated($delivery));

        return response()->json([
            'success' => true,
            'delivery' => $delivery,
        ]);
    }
}
