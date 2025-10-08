<?php

namespace App\Services\PaymentTest;

use Illuminate\Support\Facades\Log;

class CallbackNormalizationService
{
    /**
     * Normalize callback data from different payment providers
     * to a standardized format
     */
    public function normalize(string $provider, array $callbackData): array
    {
        Log::info("🔄 Normalizing callback data for provider: {$provider}", [
            'provider' => $provider,
            'raw_data_keys' => array_keys($callbackData),
        ]);

        return match ($provider) {
            'mtn' => $this->normalizeMtnCallback($callbackData),
            'orange' => $this->normalizeOrangeCallback($callbackData),
            'credit_card' => $this->normalizeCreditCardCallback($callbackData),
            default => $this->normalizeGenericCallback($callbackData),
        };
    }

    /**
     * Normalize MTN Money callback data
     */
    private function normalizeMtnCallback(array $data): array
    {
        return [
            'transaction_reference' => $data['referenceId'] ?? $data['transaction_id'] ?? null,
            'external_id' => $data['externalId'] ?? $data['external_id'] ?? null,
            'status' => $this->mapMtnStatus($data['status'] ?? 'UNKNOWN'),
            'amount' => $data['amount'] ?? null,
            'currency' => $data['currency'] ?? 'XAF',
            'reason' => $data['reason'] ?? null,
            'financial_transaction_id' => $data['financialTransactionId'] ?? null,
            'provider' => 'mtn',
            'raw_data' => $data,
        ];
    }

    /**
     * Normalize Orange Money callback data
     */
    private function normalizeOrangeCallback(array $data): array
    {
        return [
            'transaction_reference' => $data['txnid'] ?? $data['transaction_id'] ?? null,
            'external_id' => $data['merchant_txn_id'] ?? $data['external_id'] ?? null,
            'status' => $this->mapOrangeStatus($data['status'] ?? 'UNKNOWN'),
            'amount' => $data['amount'] ?? null,
            'currency' => $data['currency'] ?? 'XAF',
            'reason' => $data['message'] ?? null,
            'provider' => 'orange',
            'raw_data' => $data,
        ];
    }

    /**
     * Normalize Credit Card callback data
     */
    private function normalizeCreditCardCallback(array $data): array
    {
        return [
            'transaction_reference' => $data['transaction_id'] ?? $data['txn_ref'] ?? null,
            'external_id' => $data['merchant_ref'] ?? $data['external_id'] ?? null,
            'status' => $this->mapCreditCardStatus($data['status'] ?? 'UNKNOWN'),
            'amount' => $data['amount'] ?? null,
            'currency' => $data['currency'] ?? 'XAF',
            'reason' => $data['response_message'] ?? null,
            'provider' => 'credit_card',
            'raw_data' => $data,
        ];
    }

    /**
     * Generic normalization for unknown providers
     */
    private function normalizeGenericCallback(array $data): array
    {
        return [
            'transaction_reference' => $data['transaction_id'] ?? $data['ref'] ?? null,
            'external_id' => $data['external_id'] ?? $data['merchant_id'] ?? null,
            'status' => $data['status'] ?? 'UNKNOWN',
            'amount' => $data['amount'] ?? null,
            'currency' => $data['currency'] ?? 'XAF',
            'reason' => $data['message'] ?? $data['reason'] ?? null,
            'provider' => 'generic',
            'raw_data' => $data,
        ];
    }

    /**
     * Map MTN Money statuses to standard statuses
     */
    private function mapMtnStatus(string $status): string
    {
        return match (strtoupper($status)) {
            'SUCCESSFUL' => 'SUCCESS',
            'PENDING' => 'PENDING',
            'FAILED', 'TIMEOUT', 'CANCELLED', 'EXPIRED' => 'FAILED',
            default => 'UNKNOWN',
        };
    }

    /**
     * Map Orange Money statuses to standard statuses
     */
    private function mapOrangeStatus(string $status): string
    {
        return match (strtoupper($status)) {
            'SUCCESS', 'COMPLETED', '200' => 'SUCCESS',
            'PENDING', 'PROCESSING' => 'PENDING',
            'FAILED', 'ERROR', 'CANCELLED', 'DECLINED' => 'FAILED',
            default => 'UNKNOWN',
        };
    }

    /**
     * Map Credit Card statuses to standard statuses
     */
    private function mapCreditCardStatus(string $status): string
    {
        return match (strtoupper($status)) {
            'APPROVED', 'SUCCESS', 'COMPLETED' => 'SUCCESS',
            'PENDING', 'PROCESSING' => 'PENDING',
            'DECLINED', 'FAILED', 'ERROR', 'CANCELLED' => 'FAILED',
            default => 'UNKNOWN',
        };
    }
}