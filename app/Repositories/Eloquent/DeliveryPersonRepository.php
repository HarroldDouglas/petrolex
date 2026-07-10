<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\DTOs\Order\GetOrdersFilterDTO;
use App\Enums\OrderStatus;
use App\Models\DeliveryPerson;
use App\Repositories\Contracts\DeliveryPersonRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class DeliveryPersonRepository extends BaseEloquentRepository implements DeliveryPersonRepositoryInterface
{
    public function __construct(DeliveryPerson $model)
    {
        parent::__construct($model);
    }

    public function findLeastBusyDeliveryPerson(?int $distributionCenterId = null): ?DeliveryPerson
    {
        $query = $this->model::query()
            ->where('is_active', true)
            ->whereHas('user')
            ->withCount([
                'orders' => function ($query) {
                    $query->whereIn('status', [
                        OrderStatus::PAID(),
                        OrderStatus::PROCESSING(),
                    ]);
                },
            ]);

        // If distribution center ID is provided, filter delivery persons by that center
        if ($distributionCenterId !== null) {
            $query = $this->filterByDistributionCenter($query, $distributionCenterId);
        }

        /** @var \App\Models\DeliveryPerson|null $deliveryPerson */
        $deliveryPerson = $query
            ->orderBy('orders_count', 'asc')
            ->first();

        return $deliveryPerson;
    }

    /**
     * Filter delivery persons by distribution center
     */
    private function filterByDistributionCenter(Builder $query, int $distributionCenterId): Builder
    {
        return $query->whereHas('activeDistributionCenters', function ($q) use ($distributionCenterId) {
            $q->where('distribution_centers.id', $distributionCenterId);
        });
    }

    public function getOrdersForDeliveryPerson(DeliveryPerson $deliveryPerson, GetOrdersFilterDTO $filters, int $perPage): LengthAwarePaginator
    {
        return $deliveryPerson->orders()
            ->with([
                'items.productCategory',
                'deliveryAddress.neighborhood.municipality.city.country',
                'payment',
                'customer.user.country',
                'deliveryPerson.user',
                'distributionCenter',
                'refunds',
                'deliveryTracking',
            ])
            ->when(
                $filters->order_number !== null && $filters->order_number !== '',
                fn ($q) => $q->where('order_number', 'like', '%'.$filters->order_number.'%')
            )->when(
                $filters->status !== null,
                fn ($q) => $q->where('status', $filters->status)
            )
            ->when(
                $filters->delivery_type !== null,
                fn ($q) => $q->where('delivery_type', $filters->delivery_type)
            )
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}
