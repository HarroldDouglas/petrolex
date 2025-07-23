<?php

namespace App\Repositories\Contracts;

use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CustomerRepositoryInterface extends BaseRepositoryInterface
{
    public function getOrdersForCustomer(Customer $customer, array $filters = [], int $perPage = 10): LengthAwarePaginator;
}
