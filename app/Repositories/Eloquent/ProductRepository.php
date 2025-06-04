<?php

namespace App\Repositories\Eloquent;

use App\Models\AccessoryType;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository implements ProductRepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function getActiveProducts(): Collection
    {
        return AccessoryType::where('is_active', true)->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getInactiveProducts(): Collection
    {
        return AccessoryType::where('is_active', false)->get();
    }
}
