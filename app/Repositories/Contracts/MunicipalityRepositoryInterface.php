<?php

namespace App\Repositories\Contracts;

use App\Models\Geography\Municipality;

interface MunicipalityRepositoryInterface extends BaseRepositoryInterface
{
    public function attachNeighborhoods(Municipality $municipality, array $neighborhoodIds): void;

    public function syncNeighborhoods(Municipality $municipality, array $neighborhoodIds): void;
}
