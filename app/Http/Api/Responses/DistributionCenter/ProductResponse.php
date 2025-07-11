<?php

namespace App\Http\Api\Responses\DistributionCenter;

use App\Http\Api\Resources\ProductResource;
use App\Http\Api\Responses\ApiResponse;
use Illuminate\Support\Collection;

class ProductResponse extends ApiResponse
{
    /**
     * Return response with multiple products.
     *
     * @param  Collection|array  $products
     */
    public static function withCollection(
        Collection|array $products,
        ?string $message = null,
        int $statusCode = 200
    ): self {
        return new self(
            ProductResource::collection($products),
            $message ?? 'Produits récupérés avec succès',
            true,
            $statusCode
        );
    }
}
