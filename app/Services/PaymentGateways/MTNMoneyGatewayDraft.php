<?php

namespace App\Services\PaymentGateways;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MTNMoneyGatewayDraft
{
    protected array $config;
    protected ?string $accessToken = null;

    public function __construct()
    {
        $this->config = config('mtnmoney');
    }

    /**
     * Get OAuth access token for collection
     */
    public function getAccessToken(string $product = 'collection'): string
    {
        try {
            $endpoint = $this->config['endpoints'][$product]['token'];
            
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode($this->config['api_user'] . ':' . $this->config['api_key']),
                'Ocp-Apim-Subscription-Key' => $this->config['subscription_key']
            ])->post($this->config['base_url'] . $endpoint);

            if ($response->successful()) {
                $data = $response->json();
                $this->accessToken = $data['access_token'];
                
                Log::info('MTN MoMo: Access token obtained successfully for ' . $product);
                return $this->accessToken;
            }

            throw new Exception('Failed to get access token: ' . $response->body());

        } catch (Exception $e) {
            Log::error('MTN MoMo Token Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Process request to pay (collection) - calls MTN MoMo API
     */
    public function requestToPay(array $paymentData): array
    {
        try {
            // Get access token first
            if (!$this->accessToken) {
                $this->getAccessToken('collection');
            }

            $referenceId = Str::uuid()->toString();
            
            // Prepare data for MTN MoMo API
            $mtnRequestData = [
                'amount' => (string) $paymentData['amount'],
                'currency' => $this->config['currency'],
                'externalId' => $paymentData['external_id'] ?? uniqid('MTN_'),
                'payer' => [
                    'partyIdType' => $this->config['party_id_type'],
                    'partyId' => $this->formatPhoneNumber($paymentData['phone_number'])
                ],
                'payerMessage' => $paymentData['payer_message'] ?? $this->config['default_payer_message'],
                'payeeNote' => $paymentData['payee_note'] ?? $this->config['default_payee_note']
            ];

            // MTN API endpoint
            $mtnApiUrl = $this->config['base_url'] . $this->config['endpoints']['collection']['request_to_pay'];

            Log::info('MTN MoMo: API configuration', [
                'base_url' => $this->config['base_url'],
                'endpoint' => $this->config['endpoints']['collection']['request_to_pay'],
                'full_url' => $mtnApiUrl,
                'reference_id' => $referenceId
            ]);

            Log::info('MTN MoMo: Calling MTN API', [
                'api_url' => $mtnApiUrl,
                'reference_id' => $referenceId,
                'external_id' => $mtnRequestData['externalId'],
                'amount' => $mtnRequestData['amount'],
                'phone' => $paymentData['phone_number']
            ]);

            // Call MTN MoMo API
            $response = Http::timeout(30)->withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'X-Reference-Id' => $referenceId,
                'X-Target-Environment' => $this->config['target_environment'],
                'Ocp-Apim-Subscription-Key' => $this->config['subscription_key'],
                'Content-Type' => 'application/json'
            ])->post($mtnApiUrl, $mtnRequestData);

            if ($response->successful() || $response->status() === 202) {
                Log::info('MTN MoMo: Payment request sent successfully to MTN API', [
                    'response_status' => $response->status(),
                    'reference_id' => $referenceId,
                    'external_id' => $mtnRequestData['externalId']
                ]);

                return [
                    'success' => true,
                    'reference_id' => $referenceId,
                    'external_id' => $mtnRequestData['externalId'],
                    'status' => 'PENDING',
                    'message' => 'Payment request sent to MTN successfully',
                    'amount' => $mtnRequestData['amount'],
                    'phone_number' => $paymentData['phone_number']
                ];
            }

            throw new Exception('MTN API request failed: HTTP ' . $response->status() . ' - ' . $response->body());

        } catch (Exception $e) {
            Log::error('MTN MoMo API Error: ' . $e->getMessage(), [
                'reference_id' => $referenceId ?? 'N/A',
                'phone' => $paymentData['phone_number'] ?? 'N/A',
                'amount' => $paymentData['amount'] ?? 'N/A'
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Payment request to MTN API failed'
            ];
        }
    }

    /**
     * Get transaction status
     */
    public function getTransactionStatus(string $referenceId): array
    {
        try {
            if (!$this->accessToken) {
                $this->getAccessToken('collection');
            }

            $endpoint = str_replace('{referenceId}', $referenceId, $this->config['endpoints']['collection']['transaction_status']);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'X-Target-Environment' => $this->config['target_environment'],
                'Ocp-Apim-Subscription-Key' => $this->config['subscription_key'],
            ])->get($this->config['base_url'] . $endpoint);

            if ($response->successful()) {
                $data = $response->json();
                
                // Log detailed response for debugging
                Log::info('MTN MoMo: Detailed transaction status', [
                    'reference_id' => $referenceId,
                    'status' => $data['status'] ?? 'UNKNOWN',
                    'reason' => $data['reason'] ?? 'No reason provided',
                    'financial_transaction_id' => $data['financialTransactionId'] ?? null,
                    'full_response' => $data
                ]);

                return [
                    'success' => true,
                    'data' => $data,
                    'status' => $data['status'] ?? 'UNKNOWN',
                    'reason' => $data['reason'] ?? 'Transaction failed without specific reason',
                    'financial_transaction_id' => $data['financialTransactionId'] ?? null,
                    'external_id' => $data['externalId'] ?? null,
                    'amount' => $data['amount'] ?? null,
                    'currency' => $data['currency'] ?? null,
                    'message' => $this->getStatusMessage($data['status'] ?? 'UNKNOWN', $data['reason'] ?? null)
                ];
            }

            throw new Exception('Status check failed: ' . $response->body());

        } catch (Exception $e) {
            Log::error('MTN MoMo Status Check Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Status check failed'
            ];
        }
    }

    /**
     * Process disbursement (send money)
     */
    public function transfer(array $transferData, string $type = 'disbursement'): array
    {
        try {
            if (!$this->accessToken) {
                $this->getAccessToken($type);
            }

            $referenceId = Str::uuid()->toString();
            
            $requestData = [
                'amount' => (string) $transferData['amount'],
                'currency' => $this->config['currency'],
                'externalId' => $transferData['external_id'] ?? uniqid('MTN_'),
                'payee' => [
                    'partyIdType' => $this->config['party_id_type'],
                    'partyId' => $this->formatPhoneNumber($transferData['phone_number'])
                ],
                'payerMessage' => $transferData['payer_message'] ?? $this->config['default_payer_message'],
                'payeeNote' => $transferData['payee_note'] ?? $this->config['default_payee_note']
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'X-Reference-Id' => $referenceId,
                'X-Target-Environment' => $this->config['target_environment'],
                'Ocp-Apim-Subscription-Key' => $this->config['subscription_key'],
                'Content-Type' => 'application/json'
            ])->post($this->config['base_url'] . $this->config['endpoints'][$type]['transfer'], $requestData);

            if ($response->successful() || $response->status() === 202) {
                Log::info('MTN MoMo: Transfer initiated successfully', [
                    'type' => $type,
                    'reference_id' => $referenceId,
                    'external_id' => $requestData['externalId'],
                    'amount' => $requestData['amount']
                ]);

                return [
                    'success' => true,
                    'reference_id' => $referenceId,
                    'external_id' => $requestData['externalId'],
                    'status' => 'PENDING',
                    'message' => ucfirst($type) . ' initiated successfully'
                ];
            }

            throw new Exception(ucfirst($type) . ' failed: ' . $response->body());

        } catch (Exception $e) {
            Log::error('MTN MoMo ' . ucfirst($type) . ' Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => ucfirst($type) . ' failed'
            ];
        }
    }

    /**
     * Format phone number to international format
     */
    protected function formatPhoneNumber(string $phoneNumber): string
    {
        // Remove any non-digit characters
        $cleaned = preg_replace('/\D/', '', $phoneNumber);
        
        // If starts with 237, return as is
        if (str_starts_with($cleaned, '237')) {
            return $cleaned;
        }
        
        // If starts with 6, add 237
        if (str_starts_with($cleaned, '6')) {
            return '237' . $cleaned;
        }
        
        // Default: assume Cameroon number and add 237
        return '237' . $cleaned;
    }

    /**
     * Get user-friendly status message based on MTN response
     */
    private function getStatusMessage(string $status, ?string $reason): string
    {
        return match($status) {
            'SUCCESSFUL' => 'Payment completed successfully',
            'FAILED' => 'Payment failed: ' . ($reason ?? 'User declined, insufficient funds, or network issue'),
            'PENDING' => 'Payment is being processed - waiting for user confirmation',
            'TIMEOUT' => 'Payment timed out - user did not respond within time limit',
            'EXPIRED' => 'Payment request expired',
            'CANCELLED' => 'Payment was cancelled by user or system',
            'REJECTED' => 'Payment was rejected: ' . ($reason ?? 'Transaction not allowed'),
            default => 'Payment status: ' . $status . ($reason ? ' - ' . $reason : '')
        };
    }

    /**
     * Simulate payment for testing
     */
    public function simulatePayment(array $paymentData): array
    {
        $phoneNumber = $paymentData['phone_number'];
        $amount = $paymentData['amount'];
        $referenceId = Str::uuid()->toString();
        
        Log::info('MTN MoMo: Starting payment simulation', [
            'phone' => $phoneNumber,
            'amount' => $amount,
            'reference_id' => $referenceId
        ]);

        // Simulate processing delay
        usleep(500000); // 0.5 second delay
        
        // Simulate different responses based on test numbers
        if (in_array($phoneNumber, ['677000001', '237677000001'])) {
            Log::info('MTN MoMo: Simulating success scenario', ['phone' => $phoneNumber]);
            return [
                'success' => true,
                'status' => 'SUCCESSFUL',
                'reference_id' => $referenceId,
                'financial_transaction_id' => 'FTX_' . uniqid(),
                'external_id' => $paymentData['external_id'] ?? uniqid('MTN_'),
                'message' => 'Payment completed successfully',
                'amount' => $amount,
                'phone_number' => $phoneNumber
            ];
        }

        if (in_array($phoneNumber, ['677000002', '237677000002'])) {
            Log::info('MTN MoMo: Simulating pending scenario', ['phone' => $phoneNumber]);
            return [
                'success' => true,
                'status' => 'PENDING',
                'reference_id' => $referenceId,
                'external_id' => $paymentData['external_id'] ?? uniqid('MTN_'),
                'message' => 'Payment is being processed',
                'amount' => $amount,
                'phone_number' => $phoneNumber
            ];
        }

        if (in_array($phoneNumber, ['677000003', '237677000003'])) {
            Log::warning('MTN MoMo: Simulating failure scenario', ['phone' => $phoneNumber]);
            return [
                'success' => false,
                'status' => 'FAILED',
                'reference_id' => $referenceId,
                'error' => 'Transaction failed',
                'message' => 'Payment failed - Transaction declined',
                'amount' => $amount,
                'phone_number' => $phoneNumber
            ];
        }

        // Default success for other numbers
        Log::info('MTN MoMo: Simulating default success scenario', ['phone' => $phoneNumber]);
        return [
            'success' => true,
            'status' => 'SUCCESSFUL',
            'reference_id' => $referenceId,
            'financial_transaction_id' => 'FTX_' . uniqid(),
            'external_id' => $paymentData['external_id'] ?? uniqid('MTN_'),
            'message' => 'Payment completed successfully',
            'amount' => $amount,
            'phone_number' => $phoneNumber
        ];
    }
}