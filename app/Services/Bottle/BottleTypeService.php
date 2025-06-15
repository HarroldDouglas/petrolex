<?php

namespace App\Services\Bottle;

use App\Models\BottleType;
use App\Repositories\Contracts\BottleTypeRepositoryInterface;
use App\Services\BaseServiceForEntity;

class BottleTypeService extends BaseServiceForEntity
{
    /**
     * Create a new BottleTypeService instance.
     */
    public function __construct(BottleTypeRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    /**
     * Get the model class name
     */
    protected function getModel(): string
    {
        return BottleType::class;
    }
}
