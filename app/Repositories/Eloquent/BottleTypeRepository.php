<?php

namespace App\Repositories\Eloquent;

use App\Models\BottleType;
use App\Repositories\Contracts\BottleTypeRepositoryInterface;

class BottleTypeRepository extends BaseEloquentRepository implements BottleTypeRepositoryInterface
{
    public function __construct(BottleType $model)
    {
        parent::__construct($model);
    }
}
