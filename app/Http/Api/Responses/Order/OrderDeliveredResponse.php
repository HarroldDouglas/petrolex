<?php

declare(strict_types=1);

namespace App\Http\Api\Responses\Order;

use App\Http\Api\Resources\Order\OrderResource;
use App\Http\Api\Responses\ApiResponse;
use App\Models\Order;

final class OrderDeliveredResponse extends ApiResponse
{
    /**
     * Return response for a delivered order.
     */
    public static function delivered(
        Order $order,
        ?string $message = null,
        int $statusCode = 200
    ): self {
        return new self(
            new OrderResource($order),
            $message ?? 'Order marked as delivered successfully.',
            true,
            $statusCode
        );
    }
}
