<?php

namespace App\Repositories\Eloquent;

use App\Enums\BottleStatus;
use App\Models\Bottle;
use App\Models\BottleMovement;
use \Illuminate\Database\Eloquent\Collection;
use App\Repositories\Contracts\BottleRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BottleRepository implements BottleRepositoryInterface
{
    /**
     * Find a bottle by ID or fail
     *
     * @throws ModelNotFoundException
     */
    public function find(int $id): ?Bottle
    {
        return Bottle::findOrFail($id);
    }

    public function getBottleHistory($bottleId): Collection
    {
        return BottleMovement::where('bottle_id', $bottleId)
            ->with(['bottle', 'distributionCenter', 'deliveryPerson.user',
                'customer', 'user'])
            ->select('bottle_id', 'distribution_center_id', 'delivery_person_id',
                'customer_id', 'user_id', 'movement_date', 'notes', 'type', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function changeStatus($bottleId, BottleStatus $status): void{
        $bottle = $this->find($bottleId);
        if($status === BottleStatus::LOST_STOLEN()->value) {
            //Add Stock Movement Stolen
        }
        $bottle->update(['status'=> $status->value]);
    }
       
}
