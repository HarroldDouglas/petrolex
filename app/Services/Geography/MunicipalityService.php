<?php

namespace App\Services\Geography;

use App\Models\Geography\Municipality;
use App\Repositories\Contracts\MunicipalityRepositoryInterface;
use App\Services\BaseServiceForEntity;

class MunicipalityService extends BaseServiceForEntity
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
     * @return Municipality The found municipality model.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the municipality is not found.
     */
    public function find(int $municipalityId): Municipality
    {
        /** @var Municipality $municipality */
        $municipality = $this->municipalityRepository->find($municipalityId);

        if (! $municipality) {
            throw (new \Illuminate\Database\Eloquent\ModelNotFoundException)->setModel(Municipality::class, [$municipalityId]);
        }

        return $municipality;
    }

    /**
     * Create a new municipality.
     */
    public function createMunicipality(array $attributes, array $neighborhoodIds = []): Municipality
    {
        /** @var Municipality $municipality */
        $municipality = $this->municipalityRepository->create($attributes);

        // Ensure municipality is refreshed and has the latest data
        $municipality->refresh();

        // Attach neighborhoods if any are provided
        if (! empty($neighborhoodIds)) {
            $this->municipalityRepository->attachNeighborhoods($municipality, $neighborhoodIds);
        }

        return $municipality;
    }

    /**
     * Update an existing municipality.
     */
    public function updateMunicipality(Municipality $municipality, array $attributes, array $neighborhoodIds = []): Municipality
    {
        /** @var Municipality $municipality */
        $municipality = $this->municipalityRepository->update($municipality, $attributes);

        // Ensure municipality is refreshed and has the latest data
        $municipality->refresh();

        // Sync neighborhoods (whether empty or not)
        $this->municipalityRepository->syncNeighborhoods($municipality, $neighborhoodIds);

        return $municipality;
    }

    /**
     * Delete a municipality.
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
