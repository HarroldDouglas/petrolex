<?php

namespace App\Services\PaymentTest;

class CallbackNormalizationService
{
    /**
     * Normalize callback data based on provider format
     */
    public function normalize(string $provider, array $callbackData): array
    {
        return match (strtolower($provider)) {
            'mtn' => $this->normalizeMTNCallbackData($callbackData),
            'orange' => $this->normalizeOrangeCallbackData($callbackData),
            default => $this->normalizeGenericCallbackData($callbackData),
        };
    }

    /**
     * Normalize MTN Mobile Money callback data
     */
    private function normalizeMTNCallbackData(array $data): array
    {
        return [
            'transaction_id' => $data['financialTransactionId'] ?? 'N/A',
            'external_id' => $data['externalId'] ?? 'N/A',
            'reference_id' => $data['externalId'] ?? 'N/A',
            'status' => $data['status'] ?? 'UNKNOWN',
            'amount' => $data['amount'] ?? '0',
            'currency' => $data['currency'] ?? config('payment.defaults.currency', 'XAF'),
            'payer_phone' => $data['payer']['partyId'] ?? 'N/A',
            'payer_type' => $data['payer']['partyIdType'] ?? 'N/A',
            'reason' => $data['reason'] ?? null,
            'payer_message' => $data['payerMessage'] ?? null,
            'payee_note' => $data['payeeNote'] ?? null,
            'provider_specific' => [
                'financialTransactionId' => $data['financialTransactionId'] ?? null,
                'payer' => $data['payer'] ?? null,
            ],
        ];
    }

    /**
     * Normalize Orange Money callback data
     */
    private function normalizeOrangeCallbackData(array $data): array
    {
        return [
            'transaction_id' => $data['txnid'] ?? 'N/A',
            'external_id' => $data['payToken'] ?? 'N/A',
            'reference_id' => $data['payToken'] ?? 'N/A',
            'status' => $data['status'] ?? 'UNKNOWN',
            'amount' => 'N/A',
            'currency' => config('payment.defaults.currency', 'XAF'),
            'payer_phone' => 'N/A',
            'payer_type' => 'MSISDN',
            'reason' => $data['message'] ?? null,
            'payer_message' => null,
            'payee_note' => null,
            'provider_specific' => [
                'payToken' => $data['payToken'] ?? null,
                'txnid' => $data['txnid'] ?? null,
                'message' => $data['message'] ?? null,
            ],
        ];
    }

    /**
     * Normalize generic callback data (fallback)
     */
    private function normalizeGenericCallbackData(array $data): array
    {
        return [
            'transaction_id' => $data['transaction_id'] ?? $data['id'] ?? 'N/A',
            'external_id' => $data['external_id'] ?? $data['reference'] ?? 'N/A',
            'reference_id' => $data['reference_id'] ?? $data['reference'] ?? 'N/A',
            'status' => $data['status'] ?? 'UNKNOWN',
            'amount' => $data['amount'] ?? '0',
            'currency' => $data['currency'] ?? config('payment.defaults.currency', 'XAF'),
            'payer_phone' => 'N/A',
            'payer_type' => 'UNKNOWN',
            'reason' => $data['reason'] ?? null,
            'payer_message' => null,
            'payee_note' => null,
            'provider_specific' => $data,
        ];
    }
}
