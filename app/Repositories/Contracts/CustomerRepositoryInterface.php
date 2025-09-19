<?php

namespace App\Repositories\Contracts;

use App\DTOs\Order\GetOrdersFilterDTO;
use App\Models\Customer;
use App\Models\CustomerDeliveryAddress;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CustomerRepositoryInterface extends BaseRepositoryInterface
{
    public function getOrdersForCustomer(Customer $customer, GetOrdersFilterDTO $filters, int $perPage = 10): LengthAwarePaginator;

    public function createDeliveryAddress(array $attributes): CustomerDeliveryAddress;

    public function updateDeliveryAddress(CustomerDeliveryAddress $address, array $attributes): CustomerDeliveryAddress;
}
