<?php

namespace App\Repositories\Eloquent;

use App\DTOs\Order\GetOrdersFilterDTO;
use App\Models\Customer;
use App\Models\CustomerDeliveryAddress;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CustomerRepository extends BaseEloquentRepository implements CustomerRepositoryInterface
{
    public function __construct(Customer $customer)
    {
        parent::__construct($customer);
    }

    public function getOrdersForCustomer(Customer $customer, GetOrdersFilterDTO $filters, int $perPage = 10): LengthAwarePaginator
    {
        return $customer->orders()
            ->with([
                'items.productCategory',
                'deliveryAddress.neighborhood.municipality.city.country',
                'payment',
                'customer.deliveryAddresses.neighborhood.municipality.city.country',
            ])
            ->when(
                $filters->order_number !== null && $filters->order_number !== '',
                fn ($q) => $q->where('order_number', $filters->order_number)
            )->when(
                $filters->status !== null,
                fn ($q) => $q->where('status', $filters->status)
            )
            ->when(
                $filters->delivery_type !== null,
                fn ($q) => $q->where('delivery_type', $filters->delivery_type)
            )
            ->when(
                $filters->payment_method !== null,
                fn ($q) => $q->whereHas('payment', fn ($q) => $q->where('payment_method', $filters->payment_method))
            )
            ->paginate($perPage);
    }

    public function createDeliveryAddress(array $attributes): CustomerDeliveryAddress
    {
        $address = CustomerDeliveryAddress::create($attributes);

        if ($address->is_default) {
            CustomerDeliveryAddress::where('customer_id', $address->customer_id)
                ->where('id', '!=', $address->id)
                ->update(['is_default' => false]);
        }

        return $address->load(['neighborhood.municipality.city.country']);
    }

    public function updateDeliveryAddress(CustomerDeliveryAddress $address, array $attributes): CustomerDeliveryAddress
    {
        $address->update($attributes);

        // Handle default address logic
        if ($address->is_default) {
            CustomerDeliveryAddress::where('customer_id', $address->customer_id)
                ->where('id', '!=', $address->id)
                ->update(['is_default' => false]);
        }

        return $address->load(['neighborhood.municipality.city.country']);
    }
}
