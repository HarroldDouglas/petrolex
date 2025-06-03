<?php
namespace App\Services\Bottle;

use App\DTOs\Bottle\BottleHistoryDTO;
use App\Models\Bottle;
use App\Repositories\Bottle\BottleRepository;
use App\Repositories\Contracts\BottleRepositoryInterface;

class BottleService
{
     public function __construct(
        private BottleRepositoryInterface $bottleRepository,
     ) {
        $this->bottleRepository = $bottleRepository;
     }

    /**
     * Find a bottle by ID or fail if not found
     *
     * @param int $id
     * @return Bottle|null
     */
    public function findOrFail(int $id): Bottle
    {
        return $this->bottleRepository->findOrFail($id);
    }

    public function getBottleHistory($bottleId): array
    {
        $bottle = $this->bottleRepository->findOrFail($bottleId);
        $historyRecords = $this->bottleRepository->getBottleHistory($bottleId);
        
        return array_map(function ($history) use ($bottle) {
            return new BottleHistoryDTO(
                bottle: $bottle,
                type: $history->type,
                moved_at: $history->movement_date,
                from_location: $history->from_location,
                to_location: $history->to_location,
                user: $history->user?->name,
                notes: $history->notes
            );
        }, $historyRecords->all());
    }
    
}