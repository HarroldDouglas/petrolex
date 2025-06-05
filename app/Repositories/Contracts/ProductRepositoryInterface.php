<?php

namespace App\Repositories\Contracts;

use App\Models\AccessoryType;
use Illuminate\Database\Eloquent\Collection;

interface ProductRepositoryInterface
{
    /**
     * Get all active products
     *
     * @return Collection<AccessoryType>
     */
    public function getActiveProducts(): Collection;

    /**
     * Get all inactive products
     *
     * @return Collection<AccessoryType>
     */
    public function getInactiveProducts(): Collection;
}
