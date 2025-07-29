<?php

namespace App\Repositories\Eloquent;

use App\Models\DistributionCenter;
use App\Repositories\Contracts\DistributionCenterRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class DistributionCenterRepository extends BaseEloquentRepository implements DistributionCenterRepositoryInterface
{
    public function __construct(DistributionCenter $model)
    {
        parent::__construct($model);
    }

    /**
     * Get distribution centers by IDs.
     *
     * @param  array<int>  $ids
     * @return Collection<int, DistributionCenter>
     */
    public function getByIds(array $ids): Collection
    {
        return DistributionCenter::whereIn('id', $ids)->get();
    }

    /**
     * {@inheritDoc}
     */
    public function findWithRelation(int $id): ?DistributionCenter
    {
        return DistributionCenter::with(['bottleTypeStocks'])->find($id);
    }

    public function findClosest(float $latitude, float $longitude): ?DistributionCenter
    {
        return DistributionCenter::with(['neighborhood.municipality.city.country'])
            ->select('distribution_centers.*'
            )
            ->selectRaw(
                '(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * 
             cos(radians(longitude) - radians(?)) + 
             sin(radians(?)) * sin(radians(latitude)))) AS distance',
                [$latitude, $longitude, $latitude]
            )
            ->orderBy('distance')
            ->first();
    }
}
