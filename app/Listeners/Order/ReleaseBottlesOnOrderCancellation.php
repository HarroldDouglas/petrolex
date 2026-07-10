<?php

declare(strict_types=1);

namespace App\Listeners\Order;

use App\Enums\OrderStatus;
use App\Events\OrderStatusChanged;
use App\Listeners\BaseListener;
use App\Services\Order\OrderBottleScanService;
use Illuminate\Support\Facades\Log;

/**
 * When an order is cancelled, unlink the full bottles that were already
 * scanned for it and put them back IN_STOCK. Without this, cancelled
 * orders leave bottles stranded in WITH_DELIVERY_PERSON: they can no
 * longer be scanned for another order (which requires IN_STOCK) nor
 * received into a supply, and the stock counter (restored separately by
 * RestoreStockOnCancellationListener) diverges from the physical bottles.
 */
class ReleaseBottlesOnOrderCancellation extends BaseListener
{
    public function __construct(
        private OrderBottleScanService $orderBottleScanService
    ) {}

    /**
     * Get unique identifiers for this event
     *
     * @param  OrderStatusChanged  $event
     * @return array{order_id: int, old_status: string|null, new_status: string, event_type: string}
     */
    protected function getEventIdentifiers($event): array
    {
        return [
            'order_id' => $event->order->id,
            'old_status' => $event->oldStatus?->value,
            'new_status' => $event->newStatus->value,
            'event_type' => 'order_cancellation_bottle_release',
        ];
    }

    /**
     * Handle the event
     *
     * @param  OrderStatusChanged  $event
     */
    protected function handleEvent($event): void
    {
        if ($event->newStatus->value !== OrderStatus::CANCELLED()->value) {
            return;
        }

        $order = $event->order;

        $bottleIds = $order->items()
            ->with('bottles:bottles.id')
            ->get()
            ->flatMap(fn ($item) => $item->bottles->pluck('id'))
            ->unique()
            ->values()
            ->all();

        if ($bottleIds === []) {
            return;
        }

        // removeBottles deletes the order scans and restores every bottle
        // still WITH_DELIVERY_PERSON back to IN_STOCK, with a movement entry.
        $this->orderBottleScanService->removeBottles($order, $bottleIds);

        Log::info('[OrderCancel] bouteilles scannées libérées et remises en stock', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'bottle_ids' => $bottleIds,
        ]);
    }
}
