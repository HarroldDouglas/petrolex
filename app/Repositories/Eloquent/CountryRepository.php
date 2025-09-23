<?php

namespace App\Repositories\Eloquent;

use App\Models\Geography\Country;
use App\Repositories\Contracts\CountryRepositoryInterface;

class CountryRepository extends BaseEloquentRepository implements CountryRepositoryInterface
{
    public function __construct(Country $model)
    {
        parent::__construct($model);
    }

    public function getActiveCodeNameList(): array
    {
        return Country::query()
            ->where('is_active', true)
            ->pluck('name', 'code')
            ->toArray();
    }
}
