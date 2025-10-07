<?php

namespace App\Services\PaymentTest\Gateways;

use App\Jobs\VerifyMTNPaymentStatusJob;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

use function Illuminate\Log\log;

/**
 * MTN Money Gateway for Payment Testing
 *
 * This is the isolated test version of the MTN MoMo payment gateway.
 * All test-related functionality is contained here, separated from production code.
 */
class MTNMoneyTestGateway
{
    protected array $config;
    protected ?string $accessToken = null;
    protected string $testEnvironment;

    public function __construct(string $testEnvironment = 'sandbox')
    {
        $this->testEnvironment = $testEnvironment;
         $this->config = config('mtnmoney', []);

        if (empty($this->config)) {
            log::warning('⚠️ MTNMoneyTestGateway: mtnmoney config is empty!');
        }

        if (!isset($this->config['base_url']) || empty($this->config['base_url'])) {
            throw new \Exception('MTN MoMo base URL is not configured. Please check MTN_MOMO_BASE_URL environment variable.');
        }

        Log::info('MTN MoMo Test Gateway initialized', [
            'environment' => $testEnvironment,
            'base_url' => $this->config['base_url'],
            'config_loaded' => true,
            'config_source' => 'laravel_config',
        ]);
    }

    /**
     * Get OAuth access token for collection (test environment)
     */
    public function getAccessToken(string $product = 'collection'): string
    {
        try {
            $endpoint = $this->config['endpoints'][$product]['token'];

            Log::info('MTN MoMo Test: Requesting access token', [
                'product' => $product,
                'endpoint' => $endpoint,
                'environment' => $this->testEnvironment,
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Basic '.base64_encode($this->config['api_user'].':'.$this->config['api_key']),
                'Ocp-Apim-Subscription-Key' => $this->config['subscription_key'],
                'X-Target-Environment' => $this->config['target_environment'],
            ])->post($this->config['base_url'].$endpoint);

            if ($response->successful()) {
                $data = $response->json();
                $this->accessToken = $data['access_token'];

                Log::info('MTN MoMo Test: Access token obtained successfully', [
                    'product' => $product,
                    'token_length' => strlen($this->accessToken),
                ]);

                return $this->accessToken;
            }

            throw new Exception('Failed to get access token: '.$response->body());
        } catch (Exception $e) {
            Log::error('MTN MoMo Test Token Error: '.$e->getMessage(), [
                'product' => $product,
                'environment' => $this->testEnvironment,
            ]);
            throw $e;
        }
    }

    /**
     * Initialize payment (collection) - calls MTN MoMo API for testing
     */
    public function initPayment(array $paymentData): array
    {
        try {
            if (! $this->accessToken) {
                $this->getAccessToken('collection');
            }

            $referenceId = Str::uuid()->toString();
            $endpoint = $this->config['endpoints']['collection']['request_to_pay'];

            $formattedPhone = $this->formatPhoneNumber($paymentData['phone_number']);

            $requestData = [
                'amount' => (string) $paymentData['amount'],
                'currency' => config('payment.defaults.currency', 'XAF'),
                'externalId' => $paymentData['external_id'],
                'payer' => [
                    'partyIdType' => 'MSISDN',
                    'partyId' => $formattedPhone,
                ],
                'payerMessage' => $paymentData['reference'] ?? 'Test Payment',
                'payeeNote' => 'MTN MoMo Test Payment via Petrolex',
            ];

            Log::info('MTN MoMo Test: API configuration', [
                'base_url' => $this->config['base_url'],
                'endpoint' => $endpoint,
                'full_url' => $this->config['base_url'].$endpoint,
                'reference_id' => $referenceId,
                'test_mode' => $this->testEnvironment,
            ]);

            Log::info('MTN MoMo Test: Calling MTN API', [
                'api_url' => $this->config['base_url'].$endpoint,
                'reference_id' => $referenceId,
                'external_id' => $paymentData['external_id'],
                'amount' => $paymentData['amount'],
                'phone' => $formattedPhone,
                'test_environment' => $this->testEnvironment,
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->accessToken,
                'X-Reference-Id' => $referenceId,
                'X-Target-Environment' => $this->config['target_environment'],
                'Ocp-Apim-Subscription-Key' => $this->config['subscription_key'],
                'Content-Type' => 'application/json',
            ])->post($this->config['base_url'].$endpoint, $requestData);

            if ($response->status() === 202) {
                Log::info('MTN MoMo Test: Payment request sent successfully to MTN API', [
                    'response_status' => $response->status(),
                    'reference_id' => $referenceId,
                    'external_id' => $paymentData['external_id'],
                    'test_environment' => $this->testEnvironment,
                ]);

                VerifyMTNPaymentStatusJob::dispatch($referenceId, $this->config);
                
                Log::info('📅 MTN Payment Status Verification Job Dispatched', [
                    'reference_id' => $referenceId,
                    'external_id' => $paymentData['external_id'],
                    'verification_schedule' => 'Every 10 seconds for 3 minutes (max 18 attempts)',
                    'config_passed' => true,
                ]);

                return [
                    'success' => true,
                    'reference_id' => $referenceId,
                    'external_id' => $paymentData['external_id'],
                    'status' => 'PENDING',
                    'message' => 'Payment request sent successfully to MTN',
                    'provider' => 'MTN',
                    'amount' => $paymentData['amount'],
                    'phone' => $formattedPhone,
                    'test_environment' => $this->testEnvironment,
                    'mtn_response_status' => $response->status(),
                    'status_verification' => 'Job scheduled for automatic status checking',
                ];
            }

            throw new Exception('MTN API request failed with status: '.$response->status().' - '.$response->body());
        } catch (Exception $e) {
            Log::error('MTN MoMo Test Request Error: '.$e->getMessage(), [
                'payment_data' => $paymentData,
                'test_environment' => $this->testEnvironment,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Payment request failed',
                'provider' => 'MTN',
                'test_environment' => $this->testEnvironment,
            ];
        }
    }

    /**
     * Get transaction status for testing
     */
    public function getTransactionStatus(string $referenceId): array
    {
        try {
            if (! $this->accessToken) {
                $this->getAccessToken('collection');
            }

            $endpoint = str_replace('{referenceId}', $referenceId, $this->config['endpoints']['collection']['transaction_status']);

            Log::info('MTN MoMo Test: Checking transaction status', [
                'reference_id' => $referenceId,
                'endpoint' => $endpoint,
                'test_environment' => $this->testEnvironment,
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->accessToken,
                'X-Target-Environment' => $this->config['target_environment'],
                'Ocp-Apim-Subscription-Key' => $this->config['subscription_key'],
            ])->get($this->config['base_url'].$endpoint);

            if ($response->successful()) {
                $data = $response->json();

                Log::info('MTN MoMo Test: Detailed transaction status', [
                    'reference_id' => $referenceId,
                    'status' => $data['status'] ?? 'UNKNOWN',
                    'reason' => $data['reason'] ?? 'No reason provided',
                    'financial_transaction_id' => $data['financialTransactionId'] ?? null,
                    'full_response' => $data,
                    'test_environment' => $this->testEnvironment,
                ]);

                return [
                    'success' => true,
                    'data' => $data,
                    'status' => $data['status'] ?? 'UNKNOWN',
                    'reason' => $data['reason'] ?? 'Transaction status retrieved without specific reason',
                    'financial_transaction_id' => $data['financialTransactionId'] ?? null,
                    'external_id' => $data['externalId'] ?? null,
                    'amount' => $data['amount'] ?? null,
                    'currency' => $data['currency'] ?? null,
                    'message' => $this->getStatusMessage($data['status'] ?? 'UNKNOWN', $data['reason'] ?? null),
                    'test_environment' => $this->testEnvironment,
                ];
            }

            throw new Exception('Status check failed: '.$response->body());
        } catch (Exception $e) {
            Log::error('MTN MoMo Test Status Check Error: '.$e->getMessage(), [
                'reference_id' => $referenceId,
                'test_environment' => $this->testEnvironment,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Status check failed',
                'test_environment' => $this->testEnvironment,
            ];
        }
    }

    /**
     * Process disbursement (send money) for testing
     */
    public function transfer(array $transferData, string $type = 'disbursement'): array
    {
        try {
            Log::info('MTN MoMo Test: Processing transfer', [
                'type' => $type,
                'amount' => $transferData['amount'] ?? 'N/A',
                'phone' => $transferData['phone_number'] ?? 'N/A',
                'test_environment' => $this->testEnvironment,
            ]);

            if (! $this->accessToken) {
                $this->getAccessToken($type);
            }

            $referenceId = Str::uuid()->toString();
            $endpoint = $this->config['endpoints'][$type]['transfer'];
            $formattedPhone = $this->formatPhoneNumber($transferData['phone_number']);

            $requestData = [
                'amount' => (string) $transferData['amount'],
                'currency' => config('payment.defaults.currency', 'XAF'),
                'externalId' => $transferData['external_id'] ?? uniqid('TEST_TRANSFER_'),
                'payee' => [
                    'partyIdType' => 'MSISDN',
                    'partyId' => $formattedPhone,
                ],
                'payerMessage' => $transferData['message'] ?? 'Test Transfer',
                'payeeNote' => 'MTN MoMo Test Transfer via Petrolex',
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->accessToken,
                'X-Reference-Id' => $referenceId,
                'X-Target-Environment' => $this->config['target_environment'],
                'Ocp-Apim-Subscription-Key' => $this->config['subscription_key'],
                'Content-Type' => 'application/json',
            ])->post($this->config['base_url'].$endpoint, $requestData);

            if ($response->status() === 202) {
                Log::info('MTN MoMo Test: Transfer request successful', [
                    'reference_id' => $referenceId,
                    'type' => $type,
                    'amount' => $transferData['amount'],
                    'test_environment' => $this->testEnvironment,
                ]);

                return [
                    'success' => true,
                    'reference_id' => $referenceId,
                    'status' => 'PENDING',
                    'message' => 'Transfer initiated successfully',
                    'type' => $type,
                    'test_environment' => $this->testEnvironment,
                ];
            }

            throw new Exception('Transfer failed: '.$response->body());
        } catch (Exception $e) {
            Log::error('MTN MoMo Test Transfer Error: '.$e->getMessage(), [
                'transfer_data' => $transferData,
                'type' => $type,
                'test_environment' => $this->testEnvironment,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Transfer failed',
                'test_environment' => $this->testEnvironment,
            ];
        }
    }

    /**
     * Format phone number to international format for testing
     */
    protected function formatPhoneNumber(string $phoneNumber): string
    {
        $cleaned = preg_replace('/\D/', '', $phoneNumber);

        if (str_starts_with($cleaned, '237')) {
            return $cleaned;
        }

        if (str_starts_with($cleaned, '6')) {
            return '237'.$cleaned;
        }

        return '237'.$cleaned;
    }

    /**
     * Get user-friendly status message based on MTN response for testing
     */
    private function getStatusMessage(string $status, ?string $reason): string
    {
        return match ($status) {
            'SUCCESSFUL' => 'Test Payment completed successfully',
            'FAILED' => 'Test Payment failed: '.($reason ?? 'User declined, insufficient funds, or network issue'),
            'PENDING' => 'Test Payment is being processed - waiting for user confirmation',
            'TIMEOUT' => 'Test Payment timed out - user did not respond within time limit',
            'EXPIRED' => 'Test Payment request expired',
            'CANCELLED' => 'Test Payment was cancelled by user or system',
            'REJECTED' => 'Test Payment was rejected: '.($reason ?? 'Transaction not allowed'),
            default => 'Test Payment status: '.$status.($reason ? ' - '.$reason : '')
        };
    }

    /**
     * Simulate payment for testing (enhanced test simulation)
     */
    public function simulatePayment(array $paymentData): array
    {
        $phoneNumber = $paymentData['phone_number'];
        $amount = $paymentData['amount'];
        $referenceId = Str::uuid()->toString();

        Log::info('MTN MoMo Test: Starting enhanced payment simulation', [
            'phone' => $phoneNumber,
            'amount' => $amount,
            'reference_id' => $referenceId,
            'test_environment' => $this->testEnvironment,
        ]);

        // Enhanced simulation with longer processing delay
        usleep(800000); // 0.8 second delay for more realistic testing

        // Enhanced test scenarios based on phone numbers
        if (in_array($phoneNumber, ['677000001', '237677000001'])) {
            Log::info('MTN MoMo Test: Simulating success scenario', ['phone' => $phoneNumber]);

            return [
                'success' => true,
                'reference_id' => $referenceId,
                'external_id' => $paymentData['external_id'],
                'status' => 'SUCCESSFUL',
                'message' => 'Test payment completed successfully',
                'provider' => 'MTN',
                'test_scenario' => 'SUCCESS',
                'test_environment' => $this->testEnvironment,
            ];
        }

        if (in_array($phoneNumber, ['677000002', '237677000002'])) {
            Log::info('MTN MoMo Test: Simulating failure scenario', ['phone' => $phoneNumber]);

            return [
                'success' => false,
                'reference_id' => $referenceId,
                'external_id' => $paymentData['external_id'],
                'status' => 'FAILED',
                'message' => 'Test payment failed: Insufficient funds (simulated)',
                'error' => 'INSUFFICIENT_FUNDS',
                'provider' => 'MTN',
                'test_scenario' => 'FAILED',
                'test_environment' => $this->testEnvironment,
            ];
        }

        if (in_array($phoneNumber, ['677000003', '237677000003'])) {
            Log::info('MTN MoMo Test: Simulating pending scenario', ['phone' => $phoneNumber]);

            return [
                'success' => true,
                'reference_id' => $referenceId,
                'external_id' => $paymentData['external_id'],
                'status' => 'PENDING',
                'message' => 'Test payment is pending user confirmation',
                'provider' => 'MTN',
                'test_scenario' => 'PENDING',
                'test_environment' => $this->testEnvironment,
            ];
        }

        // Default enhanced success for other numbers
        Log::info('MTN MoMo Test: Simulating default success scenario', ['phone' => $phoneNumber]);

        return [
            'success' => true,
            'reference_id' => $referenceId,
            'external_id' => $paymentData['external_id'],
            'status' => 'SUCCESSFUL',
            'message' => 'Test payment completed successfully (default scenario)',
            'provider' => 'MTN',
            'test_scenario' => 'DEFAULT_SUCCESS',
            'test_environment' => $this->testEnvironment,
        ];
    }

    /**
     * Get test environment configuration
     */
    public function getTestConfig(): array
    {
        return [
            'environment' => $this->testEnvironment,
            'base_url' => $this->config['base_url'],
            'target_environment' => $this->config['target_environment'],
            'test_numbers' => [
                '677000001' => 'SUCCESS scenario',
                '677000002' => 'FAILED scenario',
                '677000003' => 'PENDING scenario',
                'other' => 'DEFAULT SUCCESS',
            ],
            'supported_currencies' => [config('payment.defaults.currency', 'XAF')],
            'min_amount' => 10,
            'max_amount' => 1000000,
        ];
    }

    /**
     * Request to pay (backward compatibility alias)
     */
    public function requestToPay(array $paymentData): array
    {
        return $this->initPayment($paymentData);
    }

    /**
     * Process payment (Orange-style alias for consistency)
     */
    public function processPayment(array $paymentData): array
    {
        return $this->initPayment($paymentData);
    }

    /**
     * Verify payment status with polling logic
     * 
     * @param string $referenceId The MTN reference ID to verify
     * @param int $attemptCount Current attempt number (for recursive calls)
     * @return array Verification result with status and details
     */
    public function verify(string $referenceId, int $attemptCount = 1): array
    {
        $maxAttempts = 18; // 3 minutes / 10 seconds = 18 attempts
        
        Log::info('🔍 MTN Payment Status Verification', [
            'reference_id' => $referenceId,
            'attempt' => $attemptCount,
            'max_attempts' => $maxAttempts,
        ]);

        try {
            // Get current status from MTN API
            $statusResponse = $this->getTransactionStatus($referenceId);
            
            Log::info('📊 MTN Status Response', [
                'reference_id' => $referenceId,
                'attempt' => $attemptCount,
                'response' => $statusResponse,
            ]);

            if (!$statusResponse['success']) {
                Log::warning('⚠️ MTN Status Check Failed', [
                    'reference_id' => $referenceId,
                    'attempt' => $attemptCount,
                    'error' => $statusResponse['error'] ?? 'Unknown error',
                ]);

                return [
                    'success' => false,
                    'status' => 'API_ERROR',
                    'message' => 'Status check API failed',
                    'error' => $statusResponse['error'] ?? 'Unknown API error',
                    'attempt' => $attemptCount,
                    'should_retry' => $attemptCount < $maxAttempts,
                ];
            }

            $currentStatus = strtoupper($statusResponse['status'] ?? 'UNKNOWN');
            
            // Check if status indicates success
            if ($this->isSuccessfulStatus($currentStatus)) {
                $totalTime = $attemptCount * 10; // 10 seconds per attempt
                
                Log::info('✅ MTN Payment Verification Successful', [
                    'reference_id' => $referenceId,
                    'final_status' => $currentStatus,
                    'total_attempts' => $attemptCount,
                    'total_time_seconds' => $totalTime,
                ]);

                return [
                    'success' => true,
                    'status' => $currentStatus,
                    'message' => 'Payment verified as successful',
                    'final_status' => $currentStatus,
                    'total_attempts' => $attemptCount,
                    'total_time_seconds' => $totalTime,
                    'should_retry' => false,
                    'response_data' => $statusResponse,
                ];
            }

            // Check if status indicates failure
            if ($this->isFailedStatus($currentStatus)) {
                $totalTime = $attemptCount * 10;
                
                Log::error('❌ MTN Payment Verification Failed', [
                    'reference_id' => $referenceId,
                    'final_status' => $currentStatus,
                    'total_attempts' => $attemptCount,
                    'total_time_seconds' => $totalTime,
                ]);

                return [
                    'success' => false,
                    'status' => $currentStatus,
                    'message' => 'Payment failed',
                    'final_status' => $currentStatus,
                    'total_attempts' => $attemptCount,
                    'total_time_seconds' => $totalTime,
                    'should_retry' => false,
                    'response_data' => $statusResponse,
                ];
            }

            // Status is still pending
            if ($this->isPendingStatus($currentStatus) || $currentStatus === 'UNKNOWN') {
                // Check if max attempts reached
                if ($attemptCount >= $maxAttempts) {
                    $totalTime = $maxAttempts * 10;
                    
                    Log::error('⏰ MTN Payment Verification Timeout', [
                        'reference_id' => $referenceId,
                        'final_status' => 'TIMEOUT_PENDING',
                        'max_attempts_reached' => $maxAttempts,
                        'total_time_seconds' => $totalTime,
                    ]);

                    return [
                        'success' => false,
                        'status' => 'TIMEOUT_PENDING',
                        'message' => 'Payment verification timed out - considered failed',
                        'final_status' => 'FAILED',
                        'total_attempts' => $attemptCount,
                        'total_time_seconds' => $totalTime,
                        'should_retry' => false,
                        'timeout_reason' => 'Still pending after 3 minutes',
                    ];
                }

                // Continue pending, should retry
                Log::info('⏳ MTN Payment Still Pending', [
                    'reference_id' => $referenceId,
                    'attempt' => $attemptCount,
                    'status' => $currentStatus,
                    'next_check_in' => '10 seconds',
                ]);

                return [
                    'success' => true,
                    'status' => $currentStatus,
                    'message' => 'Payment still pending, continue checking',
                    'current_status' => $currentStatus,
                    'attempt' => $attemptCount,
                    'should_retry' => true,
                    'next_attempt' => $attemptCount + 1,
                ];
            }

            // Unknown status - treat as pending but log warning
            Log::warning('⚠️ Unknown MTN Payment Status', [
                'reference_id' => $referenceId,
                'attempt' => $attemptCount,
                'unknown_status' => $currentStatus,
            ]);

            return [
                'success' => true,
                'status' => 'UNKNOWN_PENDING',
                'message' => 'Unknown status detected, treating as pending',
                'current_status' => $currentStatus,
                'attempt' => $attemptCount,
                'should_retry' => $attemptCount < $maxAttempts,
                'next_attempt' => $attemptCount + 1,
            ];

        } catch (\Exception $e) {
            Log::error('❌ MTN Payment Verification Exception', [
                'reference_id' => $referenceId,
                'attempt' => $attemptCount,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'status' => 'EXCEPTION',
                'message' => 'Verification failed due to exception',
                'error' => $e->getMessage(),
                'attempt' => $attemptCount,
                'should_retry' => $attemptCount < $maxAttempts,
            ];
        }
    }

    /**
     * Check if status indicates success
     */
    private function isSuccessfulStatus(string $status): bool
    {
        $successStatuses = config('payment.status_mappings.success_statuses', []);
        return in_array($status, $successStatuses);
    }

    /**
     * Check if status indicates failure
     */
    private function isFailedStatus(string $status): bool
    {
        $failedStatuses = config('payment.status_mappings.failed_statuses', []);
        return in_array($status, $failedStatuses);
    }

    /**
     * Check if status indicates pending
     */
    private function isPendingStatus(string $status): bool
    {
        $pendingStatuses = config('payment.status_mappings.pending_statuses', []);
        return in_array($status, $pendingStatuses);
    }
}
