<?php

namespace App\Repositories\Eloquent;

use App\Models\AccessoryType;
use App\Repositories\Contracts\AccessoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class AccessoryRepository extends BaseEloquentRepository implements AccessoryRepositoryInterface
{
    public function __construct(AccessoryType $model)
    {
        $this->model = $model;
    }

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
