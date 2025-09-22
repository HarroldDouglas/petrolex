<?php

namespace App\Http\Api\Responses\Order;

use App\Http\Api\Resources\Order\OrderDetailResource;
use App\Http\Api\Responses\ApiResponse;
use App\Models\Order;

class OrderDetailsResponse extends ApiResponse
{
    public static function withOrder(Order $order, ?string $message = null): self
    {
        return new self(
            new OrderDetailResource($order),
            $message ?? __('api.order_details_retrieved')
        );
    }
}
