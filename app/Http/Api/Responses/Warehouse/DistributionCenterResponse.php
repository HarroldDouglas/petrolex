<?php

namespace App\Http\Api\Responses\Warehouse;

use App\Http\Api\Resources\DistributionCenterResource;
use App\Http\Api\Responses\ApiResponse;
use App\Models\DistributionCenter;
use Illuminate\Support\Collection;

class DistributionCenterResponse extends ApiResponse
{
    /**
     * Return response with multiple distribution centers.
     *
     * @param  Collection<int, DistributionCenter>|array<int, DistributionCenter>  $distributionCenters
     */
    public static function withCollection(
        Collection|array $distributionCenters,
        ?string $message = null,
        int $statusCode = 200
    ): self {
        return new self(
            DistributionCenterResource::collection($distributionCenters),
            $message ?? 'Centre de distribution récupéré avec succès',
            true,
            $statusCode
        );
    }
}
