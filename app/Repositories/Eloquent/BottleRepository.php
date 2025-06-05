<?php

namespace App\Repositories\Eloquent;

use App\Enums\BottleStatus;
use App\Models\Bottle;
use App\Models\BottleMovement;
use App\Repositories\Contracts\BottleRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BottleRepository extends BaseEloquentRepository implements BottleRepositoryInterface
{
    public function __construct(Bottle $bottle)
    {
        $this->model = $bottle;
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

    public function updateStatus($bottleId, BottleStatus $status): void
    {
        $bottle = Bottle::find($bottleId);

        if (! $bottle) {
            throw new ModelNotFoundException("Bottle with ID {$bottleId} not found.");
        }
        $bottle->update(['status' => $status->value]);
    }
}
