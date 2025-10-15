<?php

namespace App\Repositories\Eloquent;

use App\Models\OrderPayment;
use App\Repositories\Contracts\OrderPaymentRepositoryInterface;

class OrderPaymentRepository extends BaseEloquentRepository implements OrderPaymentRepositoryInterface
{
    public function __construct(OrderPayment $orderPayment)
    {
        parent::__construct($orderPayment);
    }

    public function findByOrderId(string $orderId): ?OrderPayment
    {
        /** @var OrderPayment|null */
        return $this->model->where('order_id', $orderId)->first();
    }

    public function findByPaymentReference(string $paymentReference): ?OrderPayment
    {
        /** @var OrderPayment|null */
        return $this->model->where('payment_reference', $paymentReference)->first();
    }
}
