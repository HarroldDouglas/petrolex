<?php

declare(strict_types=1);

namespace App\Services\Supply;

use App\Enums\BottleStatus;
use App\Enums\SupplierDeliveryStatus;
use App\Models\Bottle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class BottleReleaseService
{
    /**
     * Release bottles stuck in PENDING_RECEPTION that are no longer held by
     * any active incoming scan belonging to an in-progress supply.
     *
     * A bottle enters PENDING_RECEPTION when scanned into a supply. If that
     * scan is later deleted — or its supply/product line is deleted or
     * cancelled — nothing reverts the status, so the bottle can never be
     * received into any other supply ("Bouteille déjà active dans le
     * système"). Bottles cannot be deleted instead (barcode is unique and
     * the model soft-deletes, which would block re-creating the barcode),
     * so they are reverted to RETURNED_TO_SUPPLIER, a state the incoming
     * scan accepts.
     *
     * @param  iterable<int>|null  $bottleIds  Restrict to these bottles; null = sweep all bottles.
     * @return int Number of bottles released.
     */
    public function releaseOrphanedBottles(?iterable $bottleIds = null): int
    {
        $query = Bottle::query()
            ->where('status', BottleStatus::PENDING_RECEPTION()->value)
            ->whereDoesntHave('supplierDeliveryBottles', function (Builder $scan): void {
                $scan->incoming()->whereHas('bottleType.supplierDelivery', function (Builder $supply): void {
                    $supply->where('status', SupplierDeliveryStatus::IN_PROGRESS()->value);
                });
            });

        if ($bottleIds !== null) {
            $ids = collect($bottleIds)->unique()->values()->all();

            if ($ids === []) {
                return 0;
            }

            $query->whereIn('id', $ids);
        }

        // is_filled must be cleared too: the empty-bottle return flow
        // (OrderService::handleEmptyBottleReturn) rejects any bottle marked
        // filled, so a released bottle left with is_filled=1 could never be
        // returned by a customer. The supply incoming scan re-sets it to true.
        $released = $query->update([
            'status' => BottleStatus::RETURNED_TO_SUPPLIER()->value,
            'is_filled' => false,
        ]);

        if ($released > 0) {
            Log::info('[BottleRelease] bouteilles orphelines libérées', [
                'count' => $released,
                'scope' => $bottleIds === null ? 'all' : 'ids',
            ]);
        }

        return $released;
    }
}
