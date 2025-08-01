<?php

declare(strict_types=1);

namespace App\Http\Api\Responses\TrackingDelivery;

use App\Http\Api\Responses\ApiResponse;
use App\Http\Resources\Api\TrackingDelivery\DeliveryTrackingResource;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpFoundation\Response;

final class DeliveryTrackingCollectionResponse extends ApiResponse
{
    /**
     * @param  Collection<int, \App\Models\DeliveryTracking>  $deliveryTrackings
     */
    public static function make(Collection $deliveryTrackings, string $message = 'Delivery trackings retrieved successfully.', int $statusCode = Response::HTTP_OK): self
    {
        return new self(
            data: DeliveryTrackingResource::collection($deliveryTrackings),
            message: $message,
            statusCode: $statusCode
        );
    }
}
