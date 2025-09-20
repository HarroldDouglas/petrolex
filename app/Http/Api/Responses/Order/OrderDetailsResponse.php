<?php

namespace App\Http\Api\Responses\Order;

use App\Http\Api\Resources\Order\OrderDetailsResource;
use App\Http\Api\Responses\ApiResponse;
use App\Models\Order;

class OrderDetailsResponse extends ApiResponse
{
    public static function withOrder(Order $order): self
    {
        return new self(
            new OrderDetailsResource($order),
            'Détails de la commande récupérés avec succès'
        );
    }
}
