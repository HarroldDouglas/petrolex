<?php

namespace App\Repositories\Contracts;

use App\Models\SupplierDelivery;
use Illuminate\Database\Eloquent\Collection;

interface SupplierDeliveryRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get an approvisionnement with its products
     */
    public function getWithProducts(int $id): ?SupplierDelivery;

    /**
     * Rechercher les approvisionnements selon des critères
     */
    public function search(array $filters): Collection;

    /**
     * Récupérer les bouteilles d'un approvisionnement spécifique
     */
    public function getBottlesForDelivery(int $deliveryId): Collection;
}
