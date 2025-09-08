<?php

namespace App\Http\Api\Controllers\Delivery;

use App\Enums\DeliveryType;
use App\Http\Api\Responses\ApiResponse;
use App\Http\Controllers\Controller;

class GetDeliveryTypesController extends Controller
{
    /**
     * Get all available delivery types.
     *
     * Route: GET /delivery-types
     */
    public function __invoke(): ApiResponse
    {
        $labels = DeliveryType::labels();

        $deliveryTypes = collect(DeliveryType::cases())
            ->map(function ($case) use ($labels) {
                return [
                    'value' => $case->value,
                    'label' => $labels[$case->value] ?? $case->value,
                    'fee' => $case->fee(),
                ];
            })->toArray();

        return ApiResponse::success(
            data: $deliveryTypes,
            message: 'Delivery types retrieved successfully.'
        );
    }
}
