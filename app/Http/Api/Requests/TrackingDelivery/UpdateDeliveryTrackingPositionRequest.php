<?php

declare(strict_types=1);

namespace App\Http\Api\Requests\TrackingDelivery;

final class UpdateDeliveryTrackingPositionRequest extends AbstractDeliveryTrackingRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'driver_lat' => 'required|numeric',
            'driver_lng' => 'required|numeric',
        ];
    }
}
