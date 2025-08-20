<?php

namespace App\Http\Api\Responses\Order;

use App\Http\Api\Resources\Order\OrderResource;
use App\Http\Api\Responses\ApiResponse;
use App\Models\Order;

class StoreOrderResponse extends ApiResponse
{
    public static function withOrder(Order $order): self
    {
        return new self(
            new OrderResource($order),
            'Commande créée avec succès'
        );
    }
}
