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
}
