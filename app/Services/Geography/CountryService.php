<?php

namespace App\Services\Geography;

use App\Models\Geography\Country;
use App\Repositories\Contracts\CountryRepositoryInterface;
use App\Services\BaseServiceForEntity;

class CountryService extends BaseServiceForEntity
{
    public function __construct(protected CountryRepositoryInterface $countryRepository)
    {
        parent::__construct($countryRepository);
    }

    /**
     * Get all active countries as [code => name]
     *
     * @return array
     */
    public function getCodeNameList(): array
    {
        return $this->countryRepository->getActiveCodeNameList();
    }

    protected function getModel(): string
    {
        return Country::class;
    }
}
