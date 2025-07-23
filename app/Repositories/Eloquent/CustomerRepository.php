<?php

namespace App\Repositories\Eloquent;

use App\Models\Customer;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CustomerRepository extends BaseEloquentRepository implements CustomerRepositoryInterface
{
    public function __construct(Customer $customer)
    {
        parent::__construct($customer);
    }

    public function getOrdersForCustomer(Customer $customer, array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $customer->orders()
            ->with(['items.productCategory', 'deliveryAddress'])
            ->when(
                ! empty($filters['order_number']),
                fn ($q) => $q->where('order_number', $filters['order_number'])
            )->when(
                ! empty($filters['status']),
                fn ($q) => $q->where('status', $filters['status'])
            )
            ->when(
                ! empty($filters['delivery_type']),
                fn ($q) => $q->where('delivery_type', $filters['delivery_type'])
            )
            ->when(
                ! empty($filters['payment_method']),
                fn ($q) => $q->whereHas('payment', fn ($q) => $q->where('payment_method', $filters['payment_method']))
            )
            ->paginate($perPage);
    }
}
