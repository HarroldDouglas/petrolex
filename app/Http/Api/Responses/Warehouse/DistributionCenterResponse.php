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
     * @param  Collection|array  $distributionCenters
     * @return static
     */
    public static function withDistributionCenters($distributionCenters): self
    {
        return new self(
            DistributionCenterResource::collection($distributionCenters),
            'Centre de distribution récupéré avec succès'
        );
    }
}
