<?php

declare(strict_types=1);

namespace App\Http\Api\Requests\TrackingDelivery;

final class CreateDeliveryTrackingRequest extends AbstractDeliveryTrackingRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'order_id' => 'required|integer|exists:orders,id',
        ];
    }
}
