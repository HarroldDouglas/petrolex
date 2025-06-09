<?php

namespace App\Services\Bottle;

use App\Models\BottleType;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class BottleTypeService
{
    /**
     * The BottleType model instance.
     *
     * @var BottleType
     */
    protected BottleType $bottleType;

    /**
     * Create a new BottleTypeService instance.
     *
     * @param BottleType $bottleType
     */
    public function __construct(BottleType $bottleType)
    {
        $this->bottleType = $bottleType;
    }

    /**
     * Find a BottleType by its ID.
     *
     * @param int 
     * @return BottleType
     * @throws ModelNotFoundException 
     */
    public function find(int $id): BottleType
    {
        try {
            return $this->bottleType->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            Log::error("BottleType not found with ID: {$id}. Error: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Update the 'is_active' status of a BottleType.
     *
     * @param int
     * @param bool
     * @return bool
     * @throws ModelNotFoundException
     */
    public function updateActiveStatus(int $id, bool $isActive): bool
    {
        try {
            $bottleType = $this->find($id);
            $bottleType->is_active = $isActive;
            return $bottleType->save();
        } catch (ModelNotFoundException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error("Error updating active status for BottleType ID: {$id}. Error: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Delete a BottleType by its ID.
     *
     * @param int 
     * @return bool
     * @throws ModelNotFoundException
     */
    public function deleteBottleType(int $id): bool
    {
        try {
            $bottleType = $this->find($id);
            return $bottleType->delete();
        } catch (ModelNotFoundException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error("Error deleting BottleType ID: {$id}. Error: {$e->getMessage()}");
            return false;
        }
    }
}