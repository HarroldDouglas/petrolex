<?php

declare(strict_types=1);

namespace App\Http\Api\Responses\Order;

use App\Http\Api\Resources\Order\OrderResource;
use App\Http\Api\Responses\ApiResponse;
use App\Models\Order;

class AddCustomerCommentToOrderResponse extends ApiResponse
{
    public static function withOrder(Order $order): self
    {
        return new self(
            new OrderResource($order),
            'Commentaire ajouté à la commande avec succès'
        );
    }
}
