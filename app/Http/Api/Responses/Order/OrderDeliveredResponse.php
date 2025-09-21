<?php

declare(strict_types=1);

namespace App\Http\Api\Responses\Order;

use App\Http\Api\Resources\Order\OrderDetailResource;
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
        bool $success = true,
        int $statusCode = 200
    ): self {

        return new self(
            new OrderDetailResource($order),
            $message,
            $success,
            $statusCode
        );
    }
}
