<?php

namespace App\Repositories\Contracts;

use App\Models\OrderPayment;

interface OrderPaymentRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find payment by order ID
     */
    public function findByOrderId(string $orderId): ?OrderPayment;

    /**
     * Find payment by payment reference
     */
    public function findByPaymentReference(string $paymentReference): ?OrderPayment;
}
