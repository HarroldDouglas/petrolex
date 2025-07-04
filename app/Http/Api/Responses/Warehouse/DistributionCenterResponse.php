<?php

namespace App\Http\Api\Responses\Warehouse;

use App\Http\Api\Resources\DistributionCenterResource;
use App\Http\Api\Responses\ApiResponse;
use Illuminate\Support\Collection;

class DistributionCenterResponse extends ApiResponse
{
    /**
     * Return response with multiple distribution centers.
     *
     * @param  Collection  $distributionCenters
     * @return self
     */
    public static function withDistributionCenters(Collection $distributionCenters): self
    {
        return new self(
            new DistributionCenterResource($distributionCenters),
            'Centre de distribution récupéré avec succès'
        );
    }
}
