<?php

namespace App\Repositories\Contracts;

use \Illuminate\Database\Eloquent\Collection;
use App\Models\Bottle;

interface BottleRepositoryInterface
{
    /**
     * Find a bottle by its ID or throw an exception if not found.
     *
     * @param int $id The ID of the bottle to find
     * @return Bottle The found bottle instance
     */
    public function findOrFail(int $id): ?Bottle;

    /**
     * Get the history of operations for a specific bottle
     *
     * @param int $bottleId The ID of the bottle
     * @return Collection The bottle's operation history
    */
    public function getBottleHistory(int $bottleId): Collection;

    /**
     * Mark a bottle record as found in the system
     * 
     * @param int|string $bottleId The ID of the bottle
     * @return void
    */
    public function markBottleAsFound($bottleId): void;
    
    /**
     * Mark a bottle record as Lost/Stolen in the system
     * 
     * @param int|string $bottleId The ID of the bottle
     * @return void
     */
    public function markBottleAsLost($bottleId): void;
    
}