<?php

namespace App\Repositories\Contracts;

use App\DTOs\Order\GetOrdersFilterDTO;
use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CustomerRepositoryInterface extends BaseRepositoryInterface
{
    public function getOrdersForCustomer(Customer $customer, GetOrdersFilterDTO $filters, int $perPage = 10): LengthAwarePaginator;
}
