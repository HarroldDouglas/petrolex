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
     * {@inheritDoc}
     */
    public function all(array $columns = ['*']): Collection
    {
        return DistributionCenter::with(['neighborhood.municipality.city.country', 'neighborhood.municipality.neighborhoods'])
            ->get($columns);
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
        return DistributionCenter::with(['neighborhood.municipality.city.country', 'neighborhood.municipality.neighborhoods'])
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
            // ensure municipality->neighborhoods are loaded so the client can propose all quartiers
            ->with(['neighborhood.municipality.city.country', 'neighborhood.municipality.neighborhoods'])
            ->get();

        if ($centersInSameMunicipality->isNotEmpty()) {
            return $centersInSameMunicipality->first();
        }

        // No center in the exact municipality — fallback to same city
        $cityId = $neighborhoodMunicipality->city_id;

        $centerInSameCity = DistributionCenter::whereHas('neighborhood.municipality', function ($query) use ($cityId) {
            $query->where('city_id', $cityId);
        })
            ->where('is_active', true)
            ->with(['neighborhood.municipality.city.country', 'neighborhood.municipality.neighborhoods'])
            ->first();

        // If still nothing, the city is not covered — return null so caller shows a clear error
        return $centerInSameCity;
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
