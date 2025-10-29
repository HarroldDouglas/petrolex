<?php

namespace App\Listeners\Order;

use App\Enums\BottleOrderType;
use App\Enums\OrderStatus;
use App\Enums\ProductType;
use App\Events\OrderStatusChanged;
use App\Listeners\BaseListener;
use App\Models\ProductCategoryDistributionCenter;
use Illuminate\Support\Facades\Log;

class DecrementStockOnPaymentListener extends BaseListener
{
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
            'event_type' => 'order_payment_stock_decrement',
        ];
    }

    /**
     * Handle the event - Decrement stock when order status changes to PAID
     *
     * @param  OrderStatusChanged  $event
     */
    protected function handleEvent($event): void
    {
        /** @var OrderStatusChanged $event */

        // Only decrement stock when order becomes PAID
        if ($event->newStatus->value !== OrderStatus::PAID()->value) {
            return;
        }

        // Only decrement if status actually changed from non-paid to paid
        if ($event->oldStatus?->value === OrderStatus::PAID()->value) {
            Log::info('DecrementStockOnPaymentListener skipped - order already paid', [
                'order_id' => $event->order->id,
                'old_status' => $event->oldStatus?->value,
                'new_status' => $event->newStatus->value,
            ]);

            return;
        }

        $order = $event->order;

        Log::info('DecrementStockOnPaymentListener triggered', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'old_status' => $event->oldStatus?->value,
            'new_status' => $event->newStatus->value,
        ]);

        // Get order items with product category
        $orderItems = $order->items()->with('productCategory')->get();

        Log::info('Processing order items for stock decrement', [
            'order_id' => $order->id,
            'items_count' => $orderItems->count(),
            'items' => $orderItems->map(fn (\App\Models\OrderItem $item) => [
                'id' => $item->id,
                'product_category_id' => $item->product_category_id,
                'quantity' => $item->quantity,
                'bottle_type' => $item->bottle_type?->value,
            ])->toArray(),
        ]);

        foreach ($orderItems as $item) {
            $this->decrementStockForItem($item, $order->distribution_center_id);
        }

        Log::info("Stock decremented for order #{$order->order_number}", [
            'order_id' => $order->id,
            'distribution_center_id' => $order->distribution_center_id,
            'items_count' => $orderItems->count(),
        ]);
    }

    /**
     * Decrement stock for a single order item
     */
    private function decrementStockForItem($item, int $distributionCenterId): void
    {
        $stockRecord = ProductCategoryDistributionCenter::where('product_category_id', $item->product_category_id)
            ->where('distribution_center_id', $distributionCenterId)
            ->lockForUpdate()
            ->first();

        if (! $stockRecord) {
            Log::warning('Stock record not found for product category', [
                'product_category_id' => $item->product_category_id,
                'distribution_center_id' => $distributionCenterId,
            ]);

            return;
        }

        $productType = $item->productCategory->product_type;

        if ($productType === ProductType::BOTTLE()) {
            // Décrement stock_filled
            $oldStockFilled = $stockRecord->stock_filled;
            $newStockFilled = max(0, $oldStockFilled - $item->quantity);

            // Si c'est une RECHARGE (contenu uniquement), increment stock_empty
            // car le livreur récupère une bouteille vide du client
            $updates = ['stock_filled' => $newStockFilled];

            if ($item->bottle_type?->value === BottleOrderType::RECHARGE()->value) {
                $oldStockEmpty = $stockRecord->stock_empty;
                $newStockEmpty = $oldStockEmpty + $item->quantity;
                $updates['stock_empty'] = $newStockEmpty;

                Log::info('Decremented bottle stock (RECHARGE - will receive empty bottles)', [
                    'product_category_id' => $item->product_category_id,
                    'quantity' => $item->quantity,
                    'old_stock_filled' => $oldStockFilled,
                    'new_stock_filled' => $newStockFilled,
                    'old_stock_empty' => $oldStockEmpty,
                    'new_stock_empty' => $newStockEmpty,
                ]);
            } else {
                Log::info('Decremented bottle stock (FULL - customer keeps bottle)', [
                    'product_category_id' => $item->product_category_id,
                    'quantity' => $item->quantity,
                    'old_stock_filled' => $oldStockFilled,
                    'new_stock_filled' => $newStockFilled,
                ]);
            }

            $stockRecord->update($updates);
        } elseif ($productType === ProductType::ACCESSORY()) {
            // For accessories, decrement stock
            $oldStock = $stockRecord->stock;
            $newStock = max(0, $oldStock - $item->quantity);
            $stockRecord->update(['stock' => $newStock]);

            Log::info('Decremented accessory stock', [
                'product_category_id' => $item->product_category_id,
                'quantity' => $item->quantity,
                'old_stock' => $oldStock,
                'new_stock' => $newStock,
            ]);
        }
    }
}
