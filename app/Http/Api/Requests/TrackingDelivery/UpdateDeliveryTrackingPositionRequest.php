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
            'current_speed' => 'nullable|numeric|min:0',

            // Données calculées côté livreur - Essentielles uniquement
            'progress_percentage' => 'nullable|numeric|min:0|max:100',
            'distance_remaining' => 'nullable|numeric|min:0',
            'estimated_duration' => 'nullable|integer|min:0',
        ];
    }
}
