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
        $locale = app()->getLocale();

        $deliveryTypes = collect(DeliveryType::cases())
            ->map(function (DeliveryType $case) use ($locale): array {
                return [
                    'value' => $case->value,
                    'label' => $case->label,
                    'fee' => $case->fee(),
                    'description' => $case->description($locale),
                ];
            })->toArray();

        return ApiResponse::success(
            data: $deliveryTypes,
            message: 'Delivery types retrieved successfully.'
        );
    }
}
