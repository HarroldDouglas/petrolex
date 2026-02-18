<?php

declare(strict_types=1);

namespace App\Services\Order;

use App\DTOs\BottleMovement\CreateBottleMovementDTO;
use App\Enums\BottleMovementType;
use App\Enums\BottleStatus;
use App\Enums\ProductType;
use App\Exceptions\BottleScanException;
use App\Models\Bottle;
use App\Models\Order;
use App\Models\OrderBottleScans;
use App\Models\OrderItem;
use App\Repositories\Contracts\BottleMovementRepositoryInterface;
use App\Repositories\Contracts\BottleRepositoryInterface;
use App\Repositories\Contracts\OrderBottleScanRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderBottleScanService
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private OrderBottleScanRepositoryInterface $orderBottleScanRepository,
        private BottleRepositoryInterface $bottleRepository,
        private BottleMovementRepositoryInterface $bottleMovementRepository
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
            ->whereHas('productCategory', function ($query) {
                $query->where('product_type', ProductType::BOTTLE());
            })
            ->with(['productCategory'])
            ->withCount('orderBottleScans')
            ->get()
            ->groupBy('productCategory.product_type_id')
            ->map(function (Collection $items) {
                return $items->first();
            })
            ->values();
    }

    /**
     * Gets all scanned bottles for a specific bottle type within an order.
     *
     * @param  Order  $order  The order containing the bottles.
     * @param  int  $bottleTypeId  The ID of the bottle type.
     * @return Collection<int, OrderBottleScans> Collection of OrderBottleScans models with related bottle data
     */
    public function getScannedBottlesByType(Order $order, int $bottleTypeId): Collection
    {
        return $this->orderBottleScanRepository->getOrderBottleScansByBottleType($order, $bottleTypeId);
    }

    /**
     * Scans and links a bottle to an order for delivery preparation.
     *
     * Validates that the bottle is in stock, filled, and belongs to the
     * order's distribution center before associating it. On success, the
     * bottle status transitions from IN_STOCK to WITH_DELIVERY_PERSON.
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
                throw new BottleScanException('Bouteille introuvable.');
            }

            $this->validateBottleEligibility($bottle, $order);

            if ($this->orderBottleScanRepository->isBottleAlreadyScanned($bottle, $order)) {
                throw new BottleScanException('Cette bouteille est déjà liée à cette commande.');
            }

            $orderItem = $this->orderBottleScanRepository->findOrderItemForBottle($order, $bottle);

            if (! $orderItem) {
                throw new BottleScanException('Aucun article correspondant à ce type de bouteille dans la commande.');
            }

            $success = $this->orderBottleScanRepository->associateBottle($orderItem, $bottle);

            if (! $success) {
                throw new BottleScanException('Erreur lors de l\'enregistrement de la bouteille.');
            }

            $this->assignBottleToDelivery($bottle, $order);

            DB::commit();

            $orderItem->refresh();
            $orderItem->loadCount('orderBottleScans');

            return $orderItem;

        } catch (BottleScanException $e) {
            DB::rollback();
            Log::warning('Bottle scan failed: '.$e->getMessage(), [
                'order_id' => $order->id,
                'barcode' => $barcode,
            ]);
            throw $e;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Unexpected error during bottle scanning', [
                'error' => $e->getMessage(),
                'order_id' => $order->id,
                'barcode' => $barcode,
            ]);
            throw new BottleScanException('Une erreur inattendue est survenue lors du scan de la bouteille.', 0, $e);
        }
    }

    /**
     * Removes scanned bottles from an order and restores their stock status.
     *
     * @param  Order  $order  The order from which to remove bottles.
     * @param  array<int>  $bottleIds  An array of bottle IDs to remove.
     *
     * @throws \Exception If there's an error during the removal process.
     */
    public function removeBottles(Order $order, array $bottleIds): void
    {
        DB::beginTransaction();
        try {
            $bottles = $this->bottleRepository->findByIds($bottleIds);

            $success = $this->orderBottleScanRepository->removeBottlesFromOrder($order, $bottleIds);

            if (! $success) {
                throw new \RuntimeException('Unable to remove bottles from order. Check repository logic or data integrity.');
            }

            foreach ($bottles as $bottle) {
                /** @var \App\Models\Bottle $bottle */
                if ($bottle->status->equals(BottleStatus::WITH_DELIVERY_PERSON())) {
                    $this->restoreBottleToStock($bottle, $order);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error removing bottles', [
                'error' => $e->getMessage(),
                'order_id' => $order->id,
                'bottle_ids' => $bottleIds,
            ]);
            throw $e;
        }
    }

    /**
     * Validates that a bottle is eligible to be linked to an order.
     *
     * @throws BottleScanException
     */
    private function validateBottleEligibility(Bottle $bottle, Order $order): void
    {
        if (! $bottle->status->equals(BottleStatus::IN_STOCK())) {
            throw new BottleScanException('Cette bouteille n\'est pas en stock (statut actuel : '.$bottle->status->label.').');
        }

        if (! $bottle->is_filled) {
            throw new BottleScanException('Cette bouteille est vide et ne peut pas être liée à une commande.');
        }

        if ($bottle->distribution_center_id !== $order->distribution_center_id) {
            throw new BottleScanException('Cette bouteille n\'appartient pas au centre de distribution de la commande.');
        }
    }

    /**
     * Transitions a bottle from IN_STOCK to WITH_DELIVERY_PERSON
     * and records the corresponding movement.
     */
    private function assignBottleToDelivery(Bottle $bottle, Order $order): void
    {
        $this->bottleRepository->update($bottle, [
            'status' => BottleStatus::WITH_DELIVERY_PERSON(),
        ]);

        $this->bottleMovementRepository->create(
            (new CreateBottleMovementDTO(
                bottleId: $bottle->id,
                type: BottleMovementType::ASSIGNMENT_TO_DELIVERY(),
                userId: (int) auth()->id(),
                movementDate: now(),
                notes: 'Bouteille liée à la commande '.$order->order_number.' pour livraison',
                distributionCenterId: $order->distribution_center_id,
                deliveryPersonId: $order->delivery_person_id,
                orderId: $order->id,
            ))->toArray()
        );
    }

    /**
     * Restores a bottle back to IN_STOCK status when unlinked from an order.
     */
    private function restoreBottleToStock(Bottle $bottle, Order $order): void
    {
        $this->bottleRepository->update($bottle, [
            'status' => BottleStatus::IN_STOCK(),
        ]);

        $this->bottleMovementRepository->create(
            (new CreateBottleMovementDTO(
                bottleId: $bottle->id,
                type: BottleMovementType::ASSIGNMENT_TO_DELIVERY(),
                userId: (int) auth()->id(),
                movementDate: now(),
                notes: 'Bouteille déliée de la commande '.$order->order_number.' — remise en stock',
                distributionCenterId: $order->distribution_center_id,
                orderId: $order->id,
            ))->toArray()
        );
    }
}
