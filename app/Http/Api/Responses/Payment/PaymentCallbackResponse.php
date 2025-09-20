<?php

declare(strict_types=1);

namespace App\Http\Api\Responses\Payment;

use App\Http\Api\Responses\ApiResponse;

final class PaymentCallbackResponse extends ApiResponse
{
    public static function paymentSuccess(string $orderId, string $status): self
    {
        $data = [
            'order_id' => $orderId,
            'transaction_status' => $status,
            'processed_at' => now()->toISOString(),
        ];

        return new self(
            $data,
            'Callback de paiement traité avec succès',
            true,
            200
        );
    }

    public static function paymentError(string $orderId, string $errorMessage): self
    {
        $data = [
            'order_id' => $orderId,
            'error' => $errorMessage,
            'processed_at' => now()->toISOString(),
        ];

        return new self(
            $data,
            'Erreur lors du traitement du callback de paiement',
            false,
            400
        );
    }
}
