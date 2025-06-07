<?php

namespace App\Services\DistributionCenter;

use App\DTOs\DistributionCenter\CreateDistributionCenterDTO;
use App\DTOs\DistributionCenter\UpdateDistributionCenter;
use App\Models\DistributionCenter;
use App\Repositories\Contracts\DistributionCenterRepositoryInterface;

class DistributionCenterService
{
    public function __construct(private DistributionCenterRepositoryInterface $distributionCenterRepository) {}

    public function create(CreateDistributionCenterDTO $dto): DistributionCenter
    {
        /** @var DistributionCenter */
        return $this->distributionCenterRepository->create($dto->toArray());
    }

    public function update(DistributionCenter $distributionCenter, UpdateDistributionCenter $dto): DistributionCenter
    {
        $this->distributionCenterRepository->update($distributionCenter, $dto->toArray());

        return $distributionCenter;
    }

    /**
     * Find a distribution center by its ID.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function find(string $id): ?DistributionCenter
    {
        return $this->distributionCenterRepository->find($id);
    }
}
