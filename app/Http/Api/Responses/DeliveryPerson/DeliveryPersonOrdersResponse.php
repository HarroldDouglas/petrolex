<?php

declare(strict_types=1);

namespace App\Http\Api\Responses\DeliveryPerson;

use App\Http\Api\Resources\Order\OrderResource;
use App\Http\Api\Responses\ApiResponse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DeliveryPersonOrdersResponse extends ApiResponse
{
    /**
     * Create a success response.
     *
     * @param  mixed  $data
     */
    public static function success($data = null, ?string $message = null, int $statusCode = 200): self
    {
        return new self($data, $message, true, $statusCode);
    }

    /**
     * Create an error response.
     *
     * @param  mixed  $data
     */
    public static function error(?string $message = null, $data = null, int $statusCode = 400): self
    {
        return new self($data, $message, false, $statusCode);
    }

    /**
     * Return response with a paginated collection of orders.
     */
    public static function paginatedCollection(LengthAwarePaginator $paginator, ?string $message = null, int $statusCode = 200): self
    {
        $response = new self(
            OrderResource::collection($paginator->items()),
            $message ?? 'Commandes du livreur récupérées avec succès',
            true,
            $statusCode
        );

        return $response->addMeta('pagination', [
            'total' => $paginator->total(),
            'current_page_total' => count($paginator->items()),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'total_pages' => $paginator->lastPage(),
            'next_page_url' => $paginator->nextPageUrl(),
            'prev_page_url' => $paginator->previousPageUrl(),
        ]);

    }
}
