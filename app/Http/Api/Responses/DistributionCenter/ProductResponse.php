<?php

namespace App\Http\Api\Responses\DistributionCenter;

use App\Http\Api\Resources\ProductResource;
use App\Http\Api\Responses\ApiResponse;
use Illuminate\Support\Collection;

class ProductResponse extends ApiResponse
{
    /**
     * Return response with multiple products.
     */
    public static function withCollection(
        Collection|array $products,
        ?int $cityId = null,
        ?string $message = null,
        int $statusCode = 200
    ): self {
        return new self(
            ProductResource::collectionForCity($products, $cityId),
            $message ?? __('messages.products_retrieved_successfully'),
            true,
            $statusCode
        );
    }
}
