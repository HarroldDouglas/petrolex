<?php

namespace App\Services\PaymentTest\Gateways;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Orange Money Gateway for Payment Testing
 *
 * This is the isolated test version of the Orange Money payment gateway.
 * All test-related functionality is contained here, separated from production code.
 */
class OrangeMoneyTestGateway
{
    protected array $config;
    protected ?string $accessToken = null;
    protected string $testEnvironment;

    public function __construct(string $testEnvironment = 'sandbox')
    {
        $this->config = config('orangemoney');
        $this->testEnvironment = $testEnvironment;

        Log::info('Orange Money Test Gateway initialized', [
            'environment' => $testEnvironment,
            'base_url' => $this->config['base_url'],
        ]);
    }

    /**
     * Get OAuth access token for test environment
     */
    public function getAccessToken(): string
    {
        try {
            Log::info('Orange Money Test: Requesting access token', [
                'client_id' => $this->config['client_id'],
                'environment' => $this->testEnvironment,
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Basic '.base64_encode($this->config['client_id'].':'.$this->config['client_secret']),
                'Content-Type' => 'application/x-www-form-urlencoded',
            ])->asForm()->post($this->config['base_url'].$this->config['endpoints']['token'], [
                'grant_type' => 'client_credentials',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->accessToken = $data['access_token'];

                Log::info('Orange Money Test: Access token obtained successfully', [
                    'token_type' => $data['token_type'] ?? 'Bearer',
                    'expires_in' => $data['expires_in'] ?? 'Unknown',
                    'test_environment' => $this->testEnvironment,
                ]);

                return $this->accessToken;
            }

            throw new Exception('Failed to get access token: '.$response->body());
        } catch (Exception $e) {
            Log::error('Orange Money Test Token Error: '.$e->getMessage(), [
                'environment' => $this->testEnvironment,
            ]);
            throw $e;
        }
    }

    /**
     * Initialize payment transaction for testing
     */
    public function initializePayment(array $paymentData): array
    {
        try {
            if (! $this->accessToken) {
                $this->getAccessToken();
            }

            $payToken = 'TEST_'.uniqid().'_'.time();
            $formattedPhone = $this->formatPhoneNumber($paymentData['phone_number']);

            $requestData = [
                'merchant_key' => $this->config['merchant_key'],
                'currency' => $this->config['currency'],
                'order_id' => $paymentData['external_id'],
                'amount' => $paymentData['amount'],
                'return_url' => $this->config['return_url'] ?? 'https://petrolex.test/callback',
                'cancel_url' => $this->config['cancel_url'] ?? 'https://petrolex.test/cancel',
                'notif_url' => $this->config['notif_url'] ?? 'https://petrolex.test/notify',
                'lang' => 'fr',
                'reference' => $paymentData['reference'] ?? 'Orange Money Test Payment',
            ];

            Log::info('Orange Money Test: Initializing payment', [
                'order_id' => $paymentData['external_id'],
                'amount' => $paymentData['amount'],
                'phone' => $formattedPhone,
                'pay_token' => $payToken,
                'test_environment' => $this->testEnvironment,
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->accessToken,
                'Content-Type' => 'application/json',
            ])->post($this->config['base_url'].$this->config['endpoints']['webpay'], $requestData);

            if ($response->successful()) {
                $data = $response->json();

                Log::info('Orange Money Test: Payment initialized successfully', [
                    'pay_token' => $data['pay_token'] ?? $payToken,
                    'payment_url' => $data['payment_url'] ?? 'N/A',
                    'test_environment' => $this->testEnvironment,
                ]);

                return [
                    'success' => true,
                    'pay_token' => $data['pay_token'] ?? $payToken,
                    'payment_url' => $data['payment_url'] ?? null,
                    'order_id' => $paymentData['external_id'],
                    'status' => 'INITIALIZED',
                    'message' => 'Orange Money test payment initialized successfully',
                    'provider' => 'ORANGE',
                    'test_environment' => $this->testEnvironment,
                ];
            }

            throw new Exception('Payment initialization failed: '.$response->body());
        } catch (Exception $e) {
            Log::error('Orange Money Test Initialization Error: '.$e->getMessage(), [
                'payment_data' => $paymentData,
                'test_environment' => $this->testEnvironment,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Payment initialization failed',
                'provider' => 'ORANGE',
                'test_environment' => $this->testEnvironment,
            ];
        }
    }

    /**
     * Initialize payment for testing (main entry point)
     */
    public function initPayment(array $paymentData): array
    {
        try {
            Log::info('Orange Money Test: Processing payment', [
                'external_id' => $paymentData['external_id'],
                'amount' => $paymentData['amount'],
                'phone' => $paymentData['phone_number'],
                'test_environment' => $this->testEnvironment,
            ]);

            // For test environment, simulate the complete flow
            if ($this->testEnvironment === 'sandbox') {
                return $this->simulatePayment($paymentData);
            }

            // For live testing, use actual Orange API
            $initResult = $this->initializePayment($paymentData);

            if ($initResult['success']) {
                // In real implementation, user would be redirected to Orange payment page
                // For testing, we return the initialization result
                return array_merge($initResult, [
                    'status' => 'PENDING',
                    'message' => 'Orange Money test payment initiated - user action required',
                    'next_step' => 'User will be redirected to Orange payment page',
                ]);
            }

            return $initResult;

        } catch (Exception $e) {
            Log::error('Orange Money Test Processing Error: '.$e->getMessage(), [
                'payment_data' => $paymentData,
                'test_environment' => $this->testEnvironment,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Orange Money test payment processing failed',
                'provider' => 'ORANGE',
                'test_environment' => $this->testEnvironment,
            ];
        }
    }

    /**
     * Process payment for testing (backward compatibility alias)
     */
    public function processPayment(array $paymentData): array
    {
        return $this->initPayment($paymentData);
    }

    /**
     * Request to pay (MTN-style alias for consistency)
     */
    public function requestToPay(array $paymentData): array
    {
        return $this->initPayment($paymentData);
    }

    /**
     * Get transaction status for testing
     */
    public function getTransactionStatus(string $transactionId): array
    {
        try {
            if (! $this->accessToken) {
                $this->getAccessToken();
            }

            Log::info('Orange Money Test: Checking transaction status', [
                'transaction_id' => $transactionId,
                'test_environment' => $this->testEnvironment,
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->accessToken,
                'Content-Type' => 'application/json',
            ])->get($this->config['base_url'].$this->config['endpoints']['status'].'/'.$transactionId);

            if ($response->successful()) {
                $data = $response->json();

                Log::info('Orange Money Test: Transaction status retrieved', [
                    'transaction_id' => $transactionId,
                    'status' => $data['status'] ?? 'UNKNOWN',
                    'amount' => $data['amount'] ?? 'N/A',
                    'test_environment' => $this->testEnvironment,
                ]);

                return [
                    'success' => true,
                    'data' => $data,
                    'status' => $data['status'] ?? 'UNKNOWN',
                    'message' => $this->getStatusMessage($data['status'] ?? 'UNKNOWN'),
                    'transaction_id' => $transactionId,
                    'amount' => $data['amount'] ?? null,
                    'currency' => $data['currency'] ?? null,
                    'test_environment' => $this->testEnvironment,
                ];
            }

            throw new Exception('Status check failed: '.$response->body());
        } catch (Exception $e) {
            Log::error('Orange Money Test Status Check Error: '.$e->getMessage(), [
                'transaction_id' => $transactionId,
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
     * Handle payment callback for testing
     */
    public function handleCallback(array $callbackData): array
    {
        try {
            Log::info('Orange Money Test: Processing callback', [
                'callback_data' => $callbackData,
                'test_environment' => $this->testEnvironment,
            ]);

            $payToken = $callbackData['pay_token'] ?? $callbackData['payToken'] ?? 'N/A';
            $status = $callbackData['status'] ?? 'UNKNOWN';
            $orderId = $callbackData['order_id'] ?? $callbackData['orderId'] ?? 'N/A';
            $amount = $callbackData['amount'] ?? 'N/A';

            $result = [
                'success' => true,
                'pay_token' => $payToken,
                'order_id' => $orderId,
                'status' => $status,
                'amount' => $amount,
                'message' => $this->getStatusMessage($status),
                'provider' => 'ORANGE',
                'test_environment' => $this->testEnvironment,
                'callback_processed' => true,
            ];

            Log::info('Orange Money Test: Callback processed successfully', $result);

            return $result;

        } catch (Exception $e) {
            Log::error('Orange Money Test Callback Error: '.$e->getMessage(), [
                'callback_data' => $callbackData,
                'test_environment' => $this->testEnvironment,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Callback processing failed',
                'test_environment' => $this->testEnvironment,
            ];
        }
    }

    /**
     * Format phone number for Orange Money testing
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
            return '237'.$cleaned;
        }

        // Default: assume Cameroon number and add 237
        return '237'.$cleaned;
    }

    /**
     * Get user-friendly status message for testing
     */
    private function getStatusMessage(string $status): string
    {
        return match (strtoupper($status)) {
            'SUCCESS', 'SUCCESSFUL', 'COMPLETED' => 'Orange Money test payment completed successfully',
            'FAILED', 'FAILURE', 'ERROR' => 'Orange Money test payment failed',
            'PENDING', 'PROCESSING', 'INITIALIZED' => 'Orange Money test payment is being processed',
            'CANCELLED', 'CANCELED' => 'Orange Money test payment was cancelled',
            'EXPIRED' => 'Orange Money test payment expired',
            default => 'Orange Money test payment status: '.$status
        };
    }

    /**
     * Simulate payment for testing (enhanced test simulation)
     */
    public function simulatePayment(array $paymentData): array
    {
        $phoneNumber = $paymentData['phone_number'];
        $amount = $paymentData['amount'];
        $payToken = 'TEST_OM_'.uniqid().'_'.time();

        Log::info('Orange Money Test: Starting enhanced payment simulation', [
            'phone' => $phoneNumber,
            'amount' => $amount,
            'pay_token' => $payToken,
            'test_environment' => $this->testEnvironment,
        ]);

        // Enhanced simulation with processing delay
        usleep(700000); // 0.7 second delay for realistic testing

        // Enhanced test scenarios based on phone numbers
        if (in_array($phoneNumber, ['655000001', '237655000001'])) {
            Log::info('Orange Money Test: Simulating success scenario', ['phone' => $phoneNumber]);

            return [
                'success' => true,
                'pay_token' => $payToken,
                'order_id' => $paymentData['external_id'],
                'status' => 'SUCCESSFUL',
                'message' => 'Orange Money test payment completed successfully',
                'provider' => 'ORANGE',
                'test_scenario' => 'SUCCESS',
                'test_environment' => $this->testEnvironment,
            ];
        }

        if (in_array($phoneNumber, ['655000002', '237655000002'])) {
            Log::info('Orange Money Test: Simulating failure scenario', ['phone' => $phoneNumber]);

            return [
                'success' => false,
                'pay_token' => $payToken,
                'order_id' => $paymentData['external_id'],
                'status' => 'FAILED',
                'message' => 'Orange Money test payment failed: Insufficient funds (simulated)',
                'error' => 'INSUFFICIENT_FUNDS',
                'provider' => 'ORANGE',
                'test_scenario' => 'FAILED',
                'test_environment' => $this->testEnvironment,
            ];
        }

        if (in_array($phoneNumber, ['655000003', '237655000003'])) {
            Log::info('Orange Money Test: Simulating pending scenario', ['phone' => $phoneNumber]);

            return [
                'success' => true,
                'pay_token' => $payToken,
                'order_id' => $paymentData['external_id'],
                'status' => 'PENDING',
                'message' => 'Orange Money test payment is pending user confirmation',
                'provider' => 'ORANGE',
                'test_scenario' => 'PENDING',
                'test_environment' => $this->testEnvironment,
            ];
        }

        // Default enhanced success for other numbers
        Log::info('Orange Money Test: Simulating default success scenario', ['phone' => $phoneNumber]);

        return [
            'success' => true,
            'pay_token' => $payToken,
            'order_id' => $paymentData['external_id'],
            'status' => 'SUCCESSFUL',
            'message' => 'Orange Money test payment completed successfully (default scenario)',
            'provider' => 'ORANGE',
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
            'currency' => $this->config['currency'],
            'merchant_key' => substr($this->config['merchant_key'], 0, 8).'***',
            'test_numbers' => [
                '655000001' => 'SUCCESS scenario',
                '655000002' => 'FAILED scenario',
                '655000003' => 'PENDING scenario',
                'other' => 'DEFAULT SUCCESS',
            ],
            'supported_currencies' => ['XAF', 'XOF'],
            'min_amount' => 10,
            'max_amount' => 1000000,
        ];
    }
}
