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
     * Get all active products
     *
     * @return Collection<AccessoryType>
     */
    public function getActiveProducts(): Collection
    {
        /** @var Collection<AccessoryType> */
        return $this->model::where('is_active', true)->get();
    }

    /**
     * Get all inactive products
     *
     * @return Collection<AccessoryType>
     */
    public function getInactiveProducts(): Collection
    {
        /** @var Collection<AccessoryType> */
        return $this->model::where('is_active', false)->get();
    }
}
