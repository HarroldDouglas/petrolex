<?php

namespace App\Services;

use App\Models\SupplierDelivery;
use App\Repositories\Contracts\SupplierDeliveryRepositoryInterface;

class SupplierDeliveryService extends BaseServiceForEntity
{
    public function __construct(SupplierDeliveryRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    protected function getModel(): string
    {
        return SupplierDelivery::class;
    }
}
