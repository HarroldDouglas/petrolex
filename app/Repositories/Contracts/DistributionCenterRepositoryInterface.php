<?php

namespace App\Repositories\Contracts;

use App\Models\DistributionCenter;
use Illuminate\Database\Eloquent\Collection;

interface DistributionCenterRepositoryInterface
{
    /**
     * Get all distribution centers.
     *
     * @return Collection<int, DistributionCenter>
     */
    public function getAll(): Collection;

    /**
     * Get distribution centers by IDs.
     *
     * @param  array<int>  $ids
     * @return Collection<int, DistributionCenter>
     */
    public function getByIds(array $ids): Collection;
}
