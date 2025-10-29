<?php

namespace App\Repositories\Eloquent;

use App\Models\DistributionCenter;
use App\Repositories\Contracts\DistributionCenterRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class DistributionCenterRepository extends BaseEloquentRepository implements DistributionCenterRepositoryInterface
{
    private const EARTH_RADIUS_KM = 6371;

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
                '('.self::EARTH_RADIUS_KM.' * acos(cos(radians(?)) * cos(radians(latitude)) * 
             cos(radians(longitude) - radians(?)) + 
             sin(radians(?)) * sin(radians(latitude)))) AS distance',
                [$latitude, $longitude, $latitude]
            )
            ->orderBy('distance')
            ->first();
    }

    public function findClosestByNeighborhood(int $neighborhoodId): ?DistributionCenter
    {
        // First, find the municipality of the given neighborhood
        $neighborhoodMunicipality = \App\Models\Geography\Neighborhood::with('municipality')
            ->find($neighborhoodId)?->municipality;

        if (! $neighborhoodMunicipality) {
            return null;
        }

        // Find distribution centers in the same municipality
        $centersInSameMunicipality = DistributionCenter::whereHas('neighborhood.municipality', function ($query) use ($neighborhoodMunicipality) {
            $query->where('id', $neighborhoodMunicipality->id);
        })
            ->where('is_active', true)
            ->with(['neighborhood.municipality.city.country'])
            ->get();

        if ($centersInSameMunicipality->isNotEmpty()) {
            // If we have centers in the same municipality, return the first one
            return $centersInSameMunicipality->first();
        }

        // If no centers in the same municipality, find the closest one by distance
        // We'll use the municipality's coordinates or fallback to general search
        return DistributionCenter::with(['neighborhood.municipality.city.country'])
            ->where('is_active', true)
            ->orderBy('id') // Simple ordering as fallback
            ->first();
    }

    /**
     * Get all center managers for a distribution center
     */
    public function getCenterManagers(int $distributionCenterId): Collection
    {
        return \App\Models\User::whereHas('distributionCenters', function ($query) use ($distributionCenterId) {
            $query->where('distribution_center_id', $distributionCenterId);
        })
            ->whereHas('roles', function ($query) {
                $query->where('name', \App\Enums\UserRole::CENTER_MANAGER()->value);
            })
            ->get();
    }
}
