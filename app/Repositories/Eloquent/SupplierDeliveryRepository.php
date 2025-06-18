<?php

namespace App\Repositories\Eloquent;

use App\Models\SupplierDelivery;
use App\Repositories\Contracts\SupplierDeliveryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class SupplierDeliveryRepository extends BaseEloquentRepository implements SupplierDeliveryRepositoryInterface
{
    public function __construct(SupplierDelivery $model)
    {
        $this->model = $model;
    }

    /**
     * Get bottles for a specific supplier delivery
     */
    public function getBottlesForDelivery(int $deliveryId): Collection
    {
        $delivery = $this->model->findOrFail($deliveryId);

        return $delivery->bottles()
            ->with(['bottleType'])
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getWithProducts(int $id): ?SupplierDelivery
    {
        return $this->model::with(['bottles', 'productTypes'])
            ->find($id);
    }

    public function search(array $filters): Collection
    {
        $query = $this->model::query();

        if (isset($filters['title']) && ! empty($filters['title'])) {
            $query->where('title', 'like', "%{$filters['title']}%");
        }

        if (isset($filters['date_from']) && ! empty($filters['date_from'])) {
            $query->where('date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to']) && ! empty($filters['date_to'])) {
            $query->where('date', '<=', $filters['date_to']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->get();
    }
}
