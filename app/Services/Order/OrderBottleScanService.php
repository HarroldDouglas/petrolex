<?php

namespace App\Services\Order;

use App\Exceptions\BottleScanException;
use App\Models\Bottle;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemBottle;
use App\Repositories\Contracts\BottleRepositoryInterface;
use App\Repositories\Contracts\OrderItemBottleRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderBottleScanService
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private OrderItemBottleRepositoryInterface $orderItemBottleRepository,
        private BottleRepositoryInterface $bottleRepository
    ) {}

    /**
     * Retrieves an order with all necessary details for scanning.
     *
     * @param  int  $orderId  The ID of the order to retrieve.
     * @return Order|null The order object if found and scannable, otherwise null.
     */
    public function getOrderForScanning(int $orderId): ?Order
    {
        $order = $this->orderRepository->getWithDetails($orderId);

        if (! $order || ! $order->canScanBottles()) {
            return null;
        }

        return $order;
    }

    /**
     * Gets order items grouped by bottle type with scan status.
     *
     * @param  Order  $order  The order for which to retrieve items.
     * @return Collection<int, OrderItem> Collection of OrderItems grouped by bottle type
     */
    public function getOrderItemsGroupedByBottleType(Order $order): Collection
    {
        return $order->items()
            ->whereHas('product.bottle')
            ->with(['product.bottle.bottleType'])
            ->withCount('orderItemBottles')
            ->get()
            ->groupBy('product.bottle.bottle_type_id')
            ->map(function (Collection $items) {
                // Return the first item from each group since they all share the same bottle type
                return $items->first();
            })
            ->values();
    }

    /**
     * Gets all scanned bottles for a specific bottle type within an order.
     *
     * @param  Order  $order  The order containing the bottles.
     * @param  int  $bottleTypeId  The ID of the bottle type.
     * @return Collection<int, OrderItemBottle> Collection of OrderItemBottle models with related bottle data
     */
    public function getScannedBottlesByType(Order $order, int $bottleTypeId): Collection
    {
        return $this->orderItemBottleRepository->getOrderItemBottlesByBottleType($order, $bottleTypeId);
    }

    /**
     * Scans a bottle for a specific order.
     *
     * @param  Order  $order  The order to which the bottle belongs.
     * @param  string  $barcode  The barcode of the bottle to scan.
     * @return OrderItem The order item that was updated with the scanned bottle.
     *
     * @throws BottleScanException If the bottle cannot be scanned due to various reasons.
     */
    public function scanBottle(Order $order, string $barcode): OrderItem
    {
        DB::beginTransaction();
        try {
            $bottle = $this->bottleRepository->findByBarcode($barcode);

            if (! $bottle) {
                throw new BottleScanException('Bottle not found.');
            }

            if ($this->orderItemBottleRepository->isBottleAlreadyScanned($bottle, $order)) {
                throw new BottleScanException('This bottle has already been scanned for this order.');
            }

            $orderItem = $this->orderItemBottleRepository->findOrderItemForBottle($order, $bottle);

            if (! $orderItem) {
                throw new BottleScanException('No matching order item for this bottle in the order.');
            }

            $success = $this->orderItemBottleRepository->associateBottle($orderItem, $bottle);

            if (! $success) {
                throw new BottleScanException('Error saving the bottle.');
            }

            DB::commit();

            // Refresh the order item to get the updated scanned_bottles_count
            $orderItem->refresh();
            $orderItem->loadCount('orderItemBottles');

            return $orderItem;

        } catch (BottleScanException $e) {
            DB::rollback();
            Log::warning('Bottle scan failed: '.$e->getMessage(), [
                'order_id' => $order->id,
                'barcode' => $barcode,
            ]);
            throw $e; // Re-throw the specific exception
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Unexpected error during bottle scanning', [
                'error' => $e->getMessage(),
                'order_id' => $order->id,
                'barcode' => $barcode,
            ]);
            throw new BottleScanException('An unexpected error occurred while scanning the bottle.', 0, $e);
        }
    }

    /**
     * Removes scanned bottles from an order.
     *
     * @param  Order  $order  The order from which to remove bottles.
     * @param  array  $bottleIds  An array of bottle IDs to remove.
     *
     * @throws \Exception If there's an error during the removal process.
     */
    public function removeBottles(Order $order, array $bottleIds): void
    {
        DB::beginTransaction();
        try {
            $success = $this->orderItemBottleRepository->removeBottlesFromOrder($order, $bottleIds);

            if (! $success) {
                // This scenario might mean a deeper issue or a business rule violation
                throw new \RuntimeException('Unable to remove bottles from order. Check repository logic or data integrity.');
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error removing bottles', [
                'error' => $e->getMessage(),
                'order_id' => $order->id,
                'bottle_ids' => $bottleIds,
            ]);
            throw $e; // Re-throw the exception to be handled by the caller
        }
    }
}
