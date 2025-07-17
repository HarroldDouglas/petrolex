<?php

namespace App\Repositories\Contracts;

use App\Models\Municipality;

interface MunicipalityRepositoryInterface extends BaseRepositoryInterface
{
    public function find(int $id): ?Municipality;
    public function create(array $attributes): Municipality;
    public function update(Municipality $municipality, array $attributes): Municipality;
    public function delete(Municipality $municipality): bool;
    public function attachNeighborhoods(Municipality $municipality, array $neighborhoodIds): void;
    public function syncNeighborhoods(Municipality $municipality, array $neighborhoodIds): void;
}
