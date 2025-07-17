<?php

namespace App\Services\Geography;

use App\Models\Neighborhood;
use App\Repositories\Contracts\NeighborhoodRepositoryInterface;
use App\Services\BaseService;

class NeighborhoodService extends BaseService
{
    public function __construct(protected NeighborhoodRepositoryInterface $neighborhoodRepository)
    {
        parent::__construct($neighborhoodRepository);
    }

    /**
     * Get all neighborhoods for a given city.
     *
     * @param  int  $cityId
     * @return \Illuminate\Database\Eloquent\Collection|Neighborhood[]
     */
    public function getNeighborhoodsByCity(int $cityId)
    {
        return $this->neighborhoodRepository->findByCity($cityId);
    }

    /**
     * Find a neighborhood by its ID.
     *
     * @param  int  $neighborhoodId
     * @return Neighborhood The found neighborhood model.
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the neighborhood is not found.
     */
    public function find(int $neighborhoodId): Neighborhood
    {
        /** @var Neighborhood $neighborhood */
        $neighborhood = $this->neighborhoodRepository->find($neighborhoodId);

        if (! $neighborhood) {
            throw (new \Illuminate\Database\Eloquent\ModelNotFoundException())->setModel(Neighborhood::class, [$neighborhoodId]);
        }

        return $neighborhood;
    }

    protected function getModel(): string
    {
        return Neighborhood::class;
    }
}
