<?php

namespace App\Services\Stock;

use App\Models\DistributionCenter;
use App\Models\ProductCategoryDistributionCenter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service centralisé pour synchroniser le stock entre:
 * - Table `bottles` (source de vérité avec traçabilité individuelle)
 * - Table `product_category_distribution_center` (cache agrégé pour performance)
 */
class StockSynchronizationService
{
    /**
     * Synchronise le stock d'une catégorie de produit dans un centre de distribution
     * à partir des vraies bouteilles dans la table `bottles`
     */
    public function synchronizeBottleStock(int $distributionCenterId, int $productCategoryId): void
    {
        Log::info('Synchronizing bottle stock', [
            'distribution_center_id' => $distributionCenterId,
            'product_category_id' => $productCategoryId,
        ]);

        // Compter les vraies bouteilles depuis la table bottles (sans les soft-deleted)
        $productIds = DB::table('products')
            ->whereNull('deleted_at')
            ->where('product_category_id', $productCategoryId)
            ->pluck('id');

        $filledCount = \App\Models\Bottle::query()
            ->where('distribution_center_id', $distributionCenterId)
            ->whereIn('product_id', $productIds)
            ->where('is_filled', true)
            ->where('status', 'in_stock')
            ->count();

        $emptyCount = \App\Models\Bottle::query()
            ->where('distribution_center_id', $distributionCenterId)
            ->whereIn('product_id', $productIds)
            ->where('is_filled', false)
            ->where('status', 'in_stock')
            ->count();

        $totalCount = $filledCount + $emptyCount;

        // Mettre à jour ou créer l'enregistrement pivot
        ProductCategoryDistributionCenter::updateOrCreate(
            [
                'product_category_id' => $productCategoryId,
                'distribution_center_id' => $distributionCenterId,
            ],
            [
                'stock_filled' => $filledCount,
                'stock_empty' => $emptyCount,
                'stock' => $totalCount,
            ]
        );

        Log::info('Bottle stock synchronized', [
            'distribution_center_id' => $distributionCenterId,
            'product_category_id' => $productCategoryId,
            'stock_filled' => $filledCount,
            'stock_empty' => $emptyCount,
            'stock_total' => $totalCount,
        ]);
    }

    /**
     * Synchronise tout le stock de bouteilles d'un centre de distribution
     */
    public function synchronizeAllBottleStockForCenter(int $distributionCenterId): void
    {
        Log::info('Synchronizing all bottle stock for distribution center', [
            'distribution_center_id' => $distributionCenterId,
        ]);

        // Récupérer toutes les catégories de bouteilles qui ont du stock dans ce centre
        $productCategoryIds = DB::table('bottles')
            ->join('products', 'bottles.product_id', '=', 'products.id')
            ->where('bottles.distribution_center_id', $distributionCenterId)
            ->distinct()
            ->pluck('products.product_category_id');

        foreach ($productCategoryIds as $productCategoryId) {
            $this->synchronizeBottleStock($distributionCenterId, $productCategoryId);
        }

        Log::info('All bottle stock synchronized for distribution center', [
            'distribution_center_id' => $distributionCenterId,
            'categories_synchronized' => $productCategoryIds->count(),
        ]);
    }

    /**
     * Synchronise tout le stock de tous les centres de distribution
     */
    public function synchronizeAllBottleStock(): void
    {
        Log::info('Synchronizing all bottle stock for all distribution centers');

        $distributionCenterIds = DistributionCenter::pluck('id');

        foreach ($distributionCenterIds as $distributionCenterId) {
            $this->synchronizeAllBottleStockForCenter($distributionCenterId);
        }

        Log::info('All bottle stock synchronized', [
            'distribution_centers_synchronized' => $distributionCenterIds->count(),
        ]);
    }
}
