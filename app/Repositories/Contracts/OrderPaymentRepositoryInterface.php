<?php

namespace App\Repositories\Contracts;

use App\Models\OrderPayment;

interface OrderPaymentRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find payment by order ID
     */
    public function findByOrderId(string $orderId): ?OrderPayment;
}