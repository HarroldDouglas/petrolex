<?php

namespace App\Http\Api\Responses\Customer;

use App\Http\Api\Resources\Order\OrderDetailResource;
use App\Http\Api\Responses\ApiResponse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CustomerOrdersResponse extends ApiResponse
{
    /**
     * Create a success response for a paginated collection of customer orders.
     */
    public static function paginatedCollection(LengthAwarePaginator $paginator, ?string $message = null, int $statusCode = 200): self
    {
        $response = new self(
            OrderDetailResource::collection($paginator->items()),
            $message ?? 'Commandes client récupérées avec succès.',
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
