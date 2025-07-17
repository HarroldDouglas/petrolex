<?php

namespace App\Services\Geography;

use App\Models\Municipality;
use App\Repositories\Contracts\MunicipalityRepositoryInterface;
use App\Services\BaseService;

class MunicipalityService extends BaseService
{
    public function __construct(protected MunicipalityRepositoryInterface $municipalityRepository)
    {
        parent::__construct($municipalityRepository);
    }

    /**
     * Get all municipalities.
     *
     * @return \Illuminate\Database\Eloquent\Collection|Municipality[]
     */
    public function getAllMunicipalities()
    {
        return $this->municipalityRepository->all();
    }

    /**
     * Find a municipality by its ID.
     *
     * @param  int  $municipalityId
     * @return Municipality The found municipality model.
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the municipality is not found.
     */
    public function find(int $municipalityId): Municipality
    {
        /** @var Municipality $municipality */
        $municipality = $this->municipalityRepository->find($municipalityId);

        if (! $municipality) {
            throw (new \Illuminate\Database\Eloquent\ModelNotFoundException())->setModel(Municipality::class, [$municipalityId]);
        }

        return $municipality;
    }

    /**
     * Create a new municipality.
     *
     * @param  array  $attributes
     * @param  array  $neighborhoodIds
     * @return Municipality
     */
    public function createMunicipality(array $attributes, array $neighborhoodIds = []): Municipality
    {
        /** @var Municipality $municipality */
        $municipality = $this->municipalityRepository->create($attributes);

        if (! empty($neighborhoodIds)) {
            $this->municipalityRepository->attachNeighborhoods($municipality, $neighborhoodIds);
        }

        return $municipality;
    }

    /**
     * Update an existing municipality.
     *
     * @param  Municipality  $municipality
     * @param  array  $attributes
     * @param  array  $neighborhoodIds
     * @return Municipality
     */
    public function updateMunicipality(Municipality $municipality, array $attributes, array $neighborhoodIds = []): Municipality
    {
        $municipality = $this->municipalityRepository->update($municipality, $attributes);

        if (! empty($neighborhoodIds)) {
            $this->municipalityRepository->syncNeighborhoods($municipality, $neighborhoodIds);
        } else {
            $this->municipalityRepository->syncNeighborhoods($municipality, []); // Detach all if none provided
        }

        return $municipality;
    }

    /**
     * Delete a municipality.
     *
     * @param  Municipality  $municipality
     * @return bool
     */
    public function deleteMunicipality(Municipality $municipality): bool
    {
        return $this->municipalityRepository->delete($municipality);
    }

    protected function getModel(): string
    {
        return Municipality::class;
    }
}
