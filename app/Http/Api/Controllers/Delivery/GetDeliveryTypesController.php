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
        $deliveryTypes = collect(DeliveryType::cases())
            ->map(fn ($case) => [
                'value' => $case->value,
                'label' => $case->label,
            ])->toArray();

        return ApiResponse::success(
            data: $deliveryTypes,
            message: 'Delivery types retrieved successfully.'
        );
    }
}
