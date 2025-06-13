<?php

namespace App\Services\DistributionCenter;

use App\DTOs\DistributionCenter\CreateDistributionCenterDTO;
use App\DTOs\DistributionCenter\UpdateDistributionCenterDTO;
use App\Events\DistributionCenterUpdatedEvent;
use App\Models\DistributionCenter;
use App\Repositories\Contracts\DistributionCenterRepositoryInterface;
use Illuminate\Support\Facades\DB;

class DistributionCenterService
{
    public function __construct(private DistributionCenterRepositoryInterface $distributionCenterRepository) {}

    /**
     * Get all distribution centers.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll()
    {
        return $this->distributionCenterRepository->all();
    }

    public function create(CreateDistributionCenterDTO $dto): DistributionCenter
    {
        /** @var DistributionCenter */
        return $this->distributionCenterRepository->create($dto->toArray());
    }

    /**
     * Update a distribution center's information
     *
     * @param  DistributionCenter  $distributionCenter  The distribution center to update
     * @param  UpdateDistributionCenterDTO  $dto  Data Transfer Object containing the updated distribution center data
     * @return DistributionCenter Whether the update was successful
     */
    public function update(DistributionCenter $distributionCenter, UpdateDistributionCenterDTO $dto): DistributionCenter
    {
        $attributes = $dto->toArrayFiltered();

        if (empty($attributes)) {
            return $distributionCenter;
        }

        $originalValues = $distributionCenter->only(array_keys($attributes));

        DB::beginTransaction();

        try {
            /** @var DistributionCenter */
            $result = $this->distributionCenterRepository->update($distributionCenter, $attributes);

            if ($result) {
                $distributionCenter->refresh();
                $currentValues = $distributionCenter->only(array_keys($attributes));
                $changes = array_diff_assoc($currentValues, $originalValues);

                if (! empty($changes)) {
                    DistributionCenterUpdatedEvent::dispatch($distributionCenter, $changes);
                }
            }

            DB::commit();

            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Find a distribution center by its ID.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function find(string $id): ?DistributionCenter
    {
        /** @var DistributionCenter|null */
        return $this->distributionCenterRepository->find($id);
    }

    /**
     * Find a distribution center by ID with relations.
     */
    public function findWithRelation(int $id): ?DistributionCenter
    {
        return $this->distributionCenterRepository->findWithRelation($id);
    }
}
