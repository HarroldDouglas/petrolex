<?php

declare(strict_types=1);

namespace App\Http\Api\Responses\Order;

use App\Http\Api\Resources\Order\OrderDetailResource;
use App\Http\Api\Responses\ApiResponse;
use App\Models\Order;

class AddCustomerCommentToOrderResponse extends ApiResponse
{
    public static function withOrder(Order $order): self
    {
        return new self(
            new OrderDetailResource($order->load([
                'customer',
                'deliveryAddress',
                'distributionCenter',
                'items.productCategory',
                'payment',
                'refunds',
                'deliveryTracking',
            ])),
            'Commentaire ajouté à la commande avec succès'
        );
    }
}
