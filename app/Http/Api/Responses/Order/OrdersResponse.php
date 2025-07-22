<?php

namespace App\Http\Api\Responses\Order;

use App\Http\Api\Resources\Order\OrderResource;
use App\Http\Api\Responses\ApiResponse;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

class OrdersResponse extends ApiResponse
{
    /**
     * Create a success response for a collection of orders.
     *
     * @param  \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order>  $orders
     */
    public static function collection(Collection $orders, ?string $message = null, int $statusCode = 200): self
    {
        return new self(
            OrderResource::collection($orders),
            $message ?? 'Liste des commandes récupérée avec succès.',
            true,
            $statusCode
        );
    }
}
