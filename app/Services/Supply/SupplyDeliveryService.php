<?php

namespace App\Services\Supply;

use App\Models\SupplierDelivery;
use App\Repositories\Contracts\SupplierDeliveryRepositoryInterface;
use App\Services\BaseServiceForEntity;
use Illuminate\Database\Eloquent\Collection;

class SupplyDeliveryService extends BaseServiceForEntity
{
    public function __construct(
        protected SupplierDeliveryRepositoryInterface $supplierDeliveryRepository
    ) {
        parent::__construct($supplierDeliveryRepository);
    }

    public function getModel(): string
    {
        return SupplierDelivery::class;
    }

    /**
     * Récupère les bouteilles associées à un approvisionnement spécifique
     */
    public function getBottlesForDelivery(int $deliveryId): Collection
    {
        return $this->supplierDeliveryRepository->getBottlesForDelivery($deliveryId);
    }
}
