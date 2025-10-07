<?php

namespace App\Services\PaymentGateways;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OrangeMoneyGatewayDraft
{
    protected array $config;
    protected ?string $accessToken = null;

    public function __construct()
    {
        $this->config = config('orangemoney');
    }

    /**
     * Get OAuth access token
     */
    public function getAccessToken(): string
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Basic '.base64_encode($this->config['client_id'].':'.$this->config['client_secret']),
                'Content-Type' => 'application/x-www-form-urlencoded',
            ])->asForm()->post($this->config['base_url'].$this->config['endpoints']['token'], [
                'grant_type' => 'client_credentials',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->accessToken = $data['access_token'];

                Log::info('Orange Money: Access token obtained successfully');

                return $this->accessToken;
            }

            throw new Exception('Failed to get access token: '.$response->body());
        } catch (Exception $e) {
            Log::error('Orange Money Token Error: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Initialize payment transaction
     */
    public function initializePayment(array $paymentData): array
    {
        try {
            if (! $this->accessToken) {
                $this->getAccessToken();
            }

            $requestData = [
                'merchant_key' => $this->config['merchant_key'],
                'currency' => 'XAF',
                'order_id' => $paymentData['external_id'] ?? uniqid('OM_'),
                'amount' => (int) $paymentData['amount'],
                'return_url' => $this->config['return_url'],
                'cancel_url' => $this->config['cancel_url'],
                'notif_url' => $this->config['notif_url'],
                'lang' => 'fr',
                'reference' => $paymentData['reference'] ?? 'Payment Petrolex',
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->accessToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($this->config['base_url'].$this->config['endpoints']['init'], $requestData);

            if ($response->successful()) {
                $data = $response->json();

                Log::info('Orange Money: Payment initialized successfully', [
                    'order_id' => $requestData['order_id'],
                    'amount' => $requestData['amount'],
                ]);

                return [
                    'success' => true,
                    'data' => $data,
                    'payment_url' => $data['payment_url'] ?? null,
                    'order_id' => $requestData['order_id'],
                    'message' => 'Payment initialized successfully',
                ];
            }

            throw new Exception('Payment initialization failed: '.$response->body());
        } catch (Exception $e) {
            Log::error('Orange Money Payment Error: '.$e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Payment initialization failed',
            ];
        }
    }

    /**
     * Process direct payment with phone number - calls external payment server
     */
    public function processPayment(array $paymentData): array
    {
        try {
            $orderId = $paymentData['external_id'] ?? uniqid('OM_');

            // Prepare data for external payment server
            $serverRequestData = [
                'merchant_key' => $this->config['merchant_key'],
                'currency' => 'XAF',
                'order_id' => $orderId,
                'amount' => (int) $paymentData['amount'],
                'phone_number' => $paymentData['phone_number'],
                'customer_email' => $paymentData['email'] ?? '',
                'customer_firstname' => $paymentData['first_name'] ?? 'Customer',
                'customer_lastname' => $paymentData['last_name'] ?? 'Petrolex',
                'provider' => 'orange',
                'callback_url' => url('/api/payment/test/orange/callback'),
                'reference' => $paymentData['reference'] ?? 'Payment Petrolex',
                'test_mode' => $paymentData['test_mode'] ?? 'sandbox',
            ];

            $baseUrl = config('services.payment_gateways.external_server.base_url');
            $endpoint = config('services.payment_gateways.external_server.orange_endpoint');
            $externalServerUrl = $baseUrl.$endpoint;
            $timeout = config('services.payment_gateways.external_server.timeout');

            Log::info('Orange Money: Calling external payment server', [
                'server_url' => $externalServerUrl,
                'order_id' => $orderId,
                'phone' => $paymentData['phone_number'],
                'amount' => $serverRequestData['amount'],
            ]);

            $response = Http::timeout($timeout)->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'User-Agent' => 'Petrolex-Payment-Test/1.0',
            ])->post($externalServerUrl, $serverRequestData);

            if ($response->successful()) {
                $responseData = $response->json();

                Log::info('Orange Money: External payment server response received', [
                    'response_status' => $response->status(),
                    'response_data' => $responseData,
                    'order_id' => $orderId,
                ]);

                return [
                    'success' => true,
                    'transaction_id' => $responseData['txnid'] ?? $responseData['transaction_id'] ?? null,
                    'order_id' => $orderId,
                    'status' => $responseData['status'] ?? 'PENDING',
                    'message' => $responseData['message'] ?? 'Payment request sent to Orange server successfully',
                    'amount' => $serverRequestData['amount'],
                    'phone_number' => $paymentData['phone_number'],
                    'server_response' => $responseData,
                ];
            }

            throw new Exception('External payment server request failed: HTTP '.$response->status().' - '.$response->body());
        } catch (Exception $e) {
            Log::error('Orange Money External Server Error: '.$e->getMessage(), [
                'order_id' => $orderId ?? 'N/A',
                'phone' => $paymentData['phone_number'] ?? 'N/A',
                'amount' => $paymentData['amount'] ?? 'N/A',
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Payment request to external server failed',
            ];
        }
    }

    /**
     * Check transaction status
     */
    public function getTransactionStatus(string $orderId): array
    {
        try {
            if (! $this->accessToken) {
                $this->getAccessToken();
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->accessToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->get($this->config['base_url'].$this->config['endpoints']['status'].$orderId);

            if ($response->successful()) {
                $data = $response->json();

                Log::info('Orange Money: Transaction status retrieved', [
                    'order_id' => $orderId,
                    'status' => $data['status'] ?? 'UNKNOWN',
                ]);

                return [
                    'success' => true,
                    'data' => $data,
                    'status' => $data['status'] ?? 'UNKNOWN',
                    'message' => 'Status retrieved successfully',
                ];
            }

            throw new Exception('Status check failed: '.$response->body());
        } catch (Exception $e) {
            Log::error('Orange Money Status Check Error: '.$e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Status check failed',
            ];
        }
    }


}
