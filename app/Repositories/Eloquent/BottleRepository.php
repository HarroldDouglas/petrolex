<?php
namespace App\Repositories\Eloquent;

use App\Models\Bottle;
use App\Models\BottleMovement;
use App\Repositories\Contracts\BottleRepositoryInterface;
use App\Enums\BottleStatus;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Collection;

class BottleRepository implements BottleRepositoryInterface
{
    /**
     * Find a bottle by ID or fail
     *
     * @param int $id
     * @return Bottle|null
     * @throws ModelNotFoundException
     */
    public function findOrFail(int $id): ?Bottle
    {
        return Bottle::findOrFail($id);
    }

    public function getBottleHistory($bottleId): Collection
    {
        return BottleMovement::where('bottle_id', $bottleId)
                            ->orderBy('created_at', 'desc')
                            ->with('user')
                            ->get();
    }

    public function markBottleAsFound($bottleId): void
    {
        $bottle = Bottle::findOrFail($bottleId);
        $bottle->status = BottleStatus::RETURNED_TO_SUPPLIER()->value;
        $bottle->save();
    }
    public function markBottleAsLost($bottleId): void
    {
        $bottle = Bottle::findOrFail($bottleId);
        $bottle->status = BottleStatus::LOST_STOLEN()->value;
        $bottle->save();
    }
}