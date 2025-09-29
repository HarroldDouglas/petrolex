<?php

namespace App\Repositories\Contracts;

interface CountryRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get all active countries as [code => name]
     */
    public function getActiveCodeNameList(): array;
}
