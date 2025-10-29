<?php

namespace App\Repositories\Contracts;

use App\Models\DistributionCenter;
use Illuminate\Database\Eloquent\Collection;

interface DistributionCenterRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get distribution centers by IDs.
     *
     * @param  array<int>  $ids
     * @return Collection<int, DistributionCenter>
     */
    public function getByIds(array $ids): Collection;

    /**
     * Find a distribution center by ID with its related bottle types.
     */
    public function findWithRelation(int $id): ?DistributionCenter;

    /**
     * Find the closest distribution center to a given point.
     */
    public function findClosest(float $latitude, float $longitude): ?DistributionCenter;

    /**
     * Find the closest distribution center to a given neighborhood.
     * Prioritizes centers in the same municipality.
     */
    public function findClosestByNeighborhood(int $neighborhoodId): ?DistributionCenter;

    /**
     * Get all center managers for a distribution center
     *
     * @param  int  $distributionCenterId
     * @return Collection<int, \App\Models\User>
     */
    public function getCenterManagers(int $distributionCenterId): Collection;
}
