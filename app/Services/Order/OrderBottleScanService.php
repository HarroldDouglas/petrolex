<?php

namespace App\Services\Order;

use App\Enums\OrderStatus;
use App\Exceptions\BottleScanException;
use App\Models\Bottle;
use App\Models\Order;
use App\Repositories\Contracts\BottleRepositoryInterface;
use App\Repositories\Contracts\OrderItemBottleRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Support\Collection;
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

        if (! $order || ! $this->canScanBottles($order)) {
            return null;
        }

        return $order;
    }

    /**
     * Checks if an order is eligible for bottle scanning.
     *
     * @param  Order  $order  The order to check.
     * @return bool True if the order can be scanned, false otherwise.
     */
    public function canScanBottles(Order $order): bool
    {
        // Uniquement les commandes confirmées avec des bouteilles sont éligibles
        return $order->status === OrderStatus::CONFIRMED() && $order->hasBottleItems();
    }

    /**
     * Gets all bottle types with their scan status for a given order.
     *
     * @param  Order  $order  The order for which to retrieve bottle types.
     * @return array An array of bottle types with their scan status.
     */
    public function getBottleTypesWithScanStatus(Order $order): array
    {
        return $this->orderItemBottleRepository->getBottleTypesWithScanStatus($order);
    }

    /**
     * Gets all scanned bottles for a specific bottle type within an order.
     *
     * @param  Order  $order  The order containing the bottles.
     * @param  int  $bottleTypeId  The ID of the bottle type.
     * @return Collection A collection of scanned bottles.
     */
    public function getScannedBottlesByType(Order $order, int $bottleTypeId): Collection
    {
        // Récupérer les IDs des éléments de commande pour ce type de bouteille
        $orderItems = $order->items()
            ->whereHas('product.bottle', function ($query) use ($bottleTypeId) {
                $query->where('bottle_type_id', $bottleTypeId);
            })
            ->pluck('id')
            ->toArray();

        if (empty($orderItems)) {
            return collect();
        }

        // Récupérer les bouteilles associées à ces éléments de commande
        return DB::table('bottles')
            ->join('order_item_bottles', 'bottles.id', '=', 'order_item_bottles.bottle_id')
            ->whereIn('order_item_bottles.order_item_id', $orderItems)
            ->select(
                'bottles.id',
                'bottles.barcode',
                'order_item_bottles.created_at as timestamp'
            )
            ->get();
    }

    /**
     * Scans a bottle for a specific order.
     *
     * @param  Order  $order  The order to which the bottle belongs.
     * @param  string  $barcode  The barcode of the bottle to scan.
     * @return array An associative array containing 'scanned_count' and 'total_count' for the affected order item.
     *
     * @throws BottleScanException If the bottle cannot be scanned due to various reasons.
     */
    public function scanBottle(Order $order, string $barcode): array
    {
        DB::beginTransaction();
        try {
            $bottle = $this->bottleRepository->findByBarcode($barcode);

            if (! $bottle) {
                throw new BottleScanException('Bouteille non trouvée.');
            }

            if ($this->orderItemBottleRepository->isBottleAlreadyScanned($bottle, $order)) {
                throw new BottleScanException('Cette bouteille a déjà été scannée pour cette commande.');
            }

            $orderItem = $this->orderItemBottleRepository->findOrderItemForBottle($order, $bottle);

            if (! $orderItem) {
                throw new BottleScanException('Aucun article correspondant pour cette bouteille dans la commande.');
            }

            $success = $this->orderItemBottleRepository->associateBottle($orderItem, $bottle);

            if (! $success) {
                throw new BottleScanException('Erreur lors de l\'enregistrement de la bouteille.');
            }

            DB::commit();

            // Refresh the order item to get the updated scanned_bottles_count
            $orderItem->refresh();

            return [
                'scanned_count' => $orderItem->scanned_bottles_count,
                'total_count' => $orderItem->quantity,
            ];

        } catch (BottleScanException $e) {
            DB::rollback();
            Log::warning('Échec du scan de bouteille: '.$e->getMessage(), [
                'order_id' => $order->id,
                'barcode' => $barcode,
            ]);
            throw $e; // Re-throw the specific exception
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Erreur inattendue lors du scan de bouteille', [
                'error' => $e->getMessage(),
                'order_id' => $order->id,
                'barcode' => $barcode,
            ]);
            throw new BottleScanException('Une erreur inattendue s\'est produite lors du scan de la bouteille.', 0, $e);
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
                throw new \RuntimeException('Impossible de supprimer les bouteilles de la commande. Vérifiez la logique du repository ou l\'intégrité des données.');
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Erreur lors de la suppression des bouteilles', [
                'error' => $e->getMessage(),
                'order_id' => $order->id,
                'bottle_ids' => $bottleIds,
            ]);
            throw $e; // Re-throw the exception to be handled by the caller
        }
    }
}
