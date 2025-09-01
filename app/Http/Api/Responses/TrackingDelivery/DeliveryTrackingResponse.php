<?php

declare(strict_types=1);

namespace App\Http\Api\Responses\TrackingDelivery;

use App\Http\Api\Responses\ApiResponse;
use App\Http\Resources\Api\TrackingDelivery\DeliveryTrackingResource;
use App\Models\DeliveryTracking;
use Symfony\Component\HttpFoundation\Response;

final class DeliveryTrackingResponse extends ApiResponse
{
    public static function make(DeliveryTracking $deliveryTracking, string $message = 'Delivery tracking operation successful.', int $statusCode = Response::HTTP_OK): self
    {
        return new self(
            data: new DeliveryTrackingResource($deliveryTracking),
            message: $message,
            statusCode: $statusCode
        );
    }
}
