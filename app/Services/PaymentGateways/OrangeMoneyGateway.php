<?php

namespace App\Services\PaymentGateways;

use App\Contracts\PaymentGateway;
use App\DTOs\PaymentCallbackData;
use App\DTOs\PaymentDetailsData;
use App\DTOs\PaymentResponse;
use App\Enums\PaymentStatus;
use App\Models\OrderPayment;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OrangeMoneyGateway implements PaymentGateway
{
    private array $config;

    private const TOKEN_CACHE_KEY = 'orange_money_access_token';

    private const TOKEN_CACHE_TTL = 3500; // Token expires in 3600s, we cache for 3500s

    public function __construct()
    {
        $this->config = config('orangemoney');
    }

    public function initiatePayment(OrderPayment $payment, PaymentDetailsData $paymentDetails): PaymentResponse
    {
        try {
            // Get phone number from payment details or customer
            $phoneNumber = $paymentDetails->getPhone()
                ?? $payment->order->customer->user->phone_number
                ?? null;

            if (! $phoneNumber) {
                return new PaymentResponse(
                    success: false,
                    status: PaymentStatus::FAILED()->value,
                    transactionReference: null,
                    paymentUrl: null,
                    amount: $payment->amount_due,
                    errorMessage: 'Phone number is required for Orange Money payment',
                    gatewayResponse: null
                );
            }

            // Clean phone number (remove +237 or 237 prefix if present)
            $phoneNumber = $this->cleanPhoneNumber($phoneNumber);

            // Step 1: Get access token
            $accessToken = $this->getAccessToken();

            if (! $accessToken) {
                return new PaymentResponse(
                    success: false,
                    status: PaymentStatus::FAILED()->value,
                    transactionReference: null,
                    paymentUrl: null,
                    amount: $payment->amount_due,
                    errorMessage: 'Failed to obtain Orange Money access token',
                    gatewayResponse: null
                );
            }

            // Step 2: Initialize payment to get payToken
            $initResponse = $this->initializePayment($accessToken, $payment);

            if (! $initResponse['success']) {
                return new PaymentResponse(
                    success: false,
                    status: PaymentStatus::FAILED()->value,
                    transactionReference: null,
                    paymentUrl: null,
                    amount: $payment->amount_due,
                    errorMessage: $initResponse['error'] ?? 'Failed to initialize Orange Money payment',
                    gatewayResponse: $initResponse
                );
            }

            $payToken = $initResponse['payToken'];

            // Step 3: Launch payment (send USSD push to customer)
            $payResponse = $this->launchPayment($accessToken, $payment, $payToken, $phoneNumber);

            if (! $payResponse['success']) {
                return new PaymentResponse(
                    success: false,
                    status: PaymentStatus::FAILED()->value,
                    transactionReference: $payToken,
                    paymentUrl: null,
                    amount: $payment->amount_due,
                    errorMessage: $payResponse['error'] ?? 'Failed to launch Orange Money payment',
                    gatewayResponse: $payResponse
                );
            }

            Log::info('Orange Money payment initiated successfully', [
                'payment_id' => $payment->id,
                'order_number' => $payment->order->order_number,
                'payToken' => $payToken,
                'txnid' => $payResponse['txnid'] ?? null,
                'phone' => $phoneNumber,
                'amount' => $payment->amount_due,
            ]);

            return new PaymentResponse(
                success: true,
                status: PaymentStatus::PENDING()->value,
                transactionReference: $payToken,
                paymentUrl: null, // Orange Money uses USSD push, no URL redirect
                amount: $payment->amount_due,
                errorMessage: null,
                gatewayResponse: [
                    'payToken' => $payToken,
                    'txnid' => $payResponse['txnid'] ?? null,
                    'status' => $payResponse['status'] ?? 'PENDING',
                    'message' => $payResponse['message'] ?? 'USSD push sent to customer',
                    'externalId' => $payment->payment_reference,
                ]
            );

        } catch (\Exception $e) {
            Log::error('Orange Money payment error', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return new PaymentResponse(
                success: false,
                status: PaymentStatus::FAILED()->value,
                transactionReference: null,
                paymentUrl: null,
                amount: $payment->amount_due,
                errorMessage: 'Orange Money payment error: '.$e->getMessage(),
                gatewayResponse: null
            );
        }
    }

    public function verifyPayment(string $transactionReference): PaymentResponse
    {
        try {
            $accessToken = $this->getAccessToken();

            if (! $accessToken) {
                return new PaymentResponse(
                    success: false,
                    status: PaymentStatus::PENDING()->value,
                    transactionReference: $transactionReference,
                    paymentUrl: null,
                    amount: null,
                    errorMessage: 'Failed to obtain access token for verification',
                    gatewayResponse: null
                );
            }

            $statusUrl = $this->config['base_url'].$this->config['endpoints']['status'].$transactionReference;

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$accessToken,
                'X-AUTH-TOKEN' => $this->getXAuthToken(),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->get($statusUrl);

            $data = $response->json();

            Log::info('Orange Money payment status check', [
                'payToken' => $transactionReference,
                'response' => $data,
            ]);

            if (! $response->successful()) {
                return new PaymentResponse(
                    success: false,
                    status: PaymentStatus::PENDING()->value,
                    transactionReference: $transactionReference,
                    paymentUrl: null,
                    amount: null,
                    errorMessage: $data['message'] ?? 'Status check failed',
                    gatewayResponse: $data
                );
            }

            $status = $data['data']['status'] ?? 'PENDING';
            $paymentStatus = $this->mapOrangeStatusToPaymentStatus($status);

            return new PaymentResponse(
                success: $paymentStatus === PaymentStatus::PAID()->value,
                status: $paymentStatus,
                transactionReference: $transactionReference,
                paymentUrl: null,
                amount: $data['data']['amount'] ?? null,
                errorMessage: null,
                gatewayResponse: [
                    'payToken' => $transactionReference,
                    'txnid' => $data['data']['txnid'] ?? null,
                    'status' => $status,
                    'orderId' => $data['data']['orderId'] ?? null,
                    'externalId' => $data['data']['orderId'] ?? null,
                    'confirmtxnmessage' => $data['data']['confirmtxnmessage'] ?? null,
                ]
            );

        } catch (\Exception $e) {
            Log::error('Orange Money status verification error', [
                'payToken' => $transactionReference,
                'error' => $e->getMessage(),
            ]);

            return new PaymentResponse(
                success: false,
                status: PaymentStatus::PENDING()->value,
                transactionReference: $transactionReference,
                paymentUrl: null,
                amount: null,
                errorMessage: $e->getMessage(),
                gatewayResponse: null
            );
        }
    }

    public function handleCallback(PaymentCallbackData $callbackData): PaymentResponse
    {
        return new PaymentResponse(
            success: $callbackData->status === PaymentStatus::PAID()->value,
            status: $callbackData->status,
            transactionReference: $callbackData->transactionReference,
            paymentUrl: null,
            amount: $callbackData->amount,
            errorMessage: null,
            gatewayResponse: $callbackData->rawData
        );
    }

    public function supports(string $paymentMethod): bool
    {
        return $paymentMethod === 'orange_money';
    }

    /**
     * Get OAuth access token (cached)
     */
    private function getAccessToken(): ?string
    {
        // Try to get from cache first
        $cachedToken = Cache::get(self::TOKEN_CACHE_KEY);

        if ($cachedToken) {
            return $cachedToken;
        }

        try {
            $tokenUrl = $this->config['base_url'].$this->config['endpoints']['token'];

            $credentials = base64_encode($this->config['client_id'].':'.$this->config['client_secret']);

            $response = Http::withHeaders([
                'Authorization' => 'Basic '.$credentials,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ])->asForm()->post($tokenUrl, [
                'grant_type' => 'client_credentials',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $token = $data['access_token'];

                // Cache the token
                Cache::put(self::TOKEN_CACHE_KEY, $token, self::TOKEN_CACHE_TTL);

                Log::info('Orange Money access token obtained successfully');

                return $token;
            }

            Log::error('Failed to get Orange Money access token', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('Orange Money token error', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Initialize payment to get payToken
     */
    private function initializePayment(string $accessToken, OrderPayment $payment): array
    {
        try {
            $initUrl = $this->config['base_url'].$this->config['endpoints']['init'];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$accessToken,
                'X-AUTH-TOKEN' => $this->getXAuthToken(),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($initUrl, [
                'amount' => (string) intval($payment->amount_due),
                'currency' => 'XAF',
                'orderId' => $payment->payment_reference,
                'description' => $this->config['description'] ?? 'Paiement Petrolex',
            ]);

            $data = $response->json();

            if ($response->successful() && isset($data['data']['payToken'])) {
                return [
                    'success' => true,
                    'payToken' => $data['data']['payToken'],
                    'message' => $data['message'] ?? 'Payment initialized',
                ];
            }

            return [
                'success' => false,
                'error' => $data['message'] ?? 'Init failed',
                'response' => $data,
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Launch payment (send USSD push to customer)
     */
    private function launchPayment(string $accessToken, OrderPayment $payment, string $payToken, string $phoneNumber): array
    {
        try {
            $payUrl = $this->config['base_url'].$this->config['endpoints']['pay'];

            $notifUrl = $this->config['notif_url'] ?? config('app.url').'/api/payment/callback/orange';

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$accessToken,
                'X-AUTH-TOKEN' => $this->getXAuthToken(),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($payUrl, [
                'notifUrl' => $notifUrl,
                'channelUserMsisdn' => $this->config['channel_user_msisdn'],
                'amount' => (string) intval($payment->amount_due),
                'subscriberMsisdn' => $phoneNumber,
                'pin' => $this->config['pin'],
                'orderId' => $payment->payment_reference,
                'description' => $this->config['description'] ?? 'Paiement Petrolex',
                'payToken' => $payToken,
            ]);

            $data = $response->json();

            if ($response->successful() && isset($data['data'])) {
                return [
                    'success' => true,
                    'status' => $data['data']['status'] ?? 'PENDING',
                    'txnid' => $data['data']['txnid'] ?? null,
                    'message' => $data['message'] ?? 'Payment initiated',
                    'response' => $data,
                ];
            }

            return [
                'success' => false,
                'error' => $data['message'] ?? 'Pay request failed',
                'response' => $data,
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate X-AUTH-TOKEN header value
     */
    private function getXAuthToken(): string
    {
        return base64_encode($this->config['api_username'].':'.$this->config['api_password']);
    }

    /**
     * Clean phone number (remove country code prefix)
     */
    private function cleanPhoneNumber(string $phone): string
    {
        // Remove spaces, dashes, etc.
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Remove +237 or 237 prefix if present
        if (str_starts_with($phone, '237') && strlen($phone) === 12) {
            $phone = substr($phone, 3);
        }

        return $phone;
    }

    /**
     * Map Orange Money status to PaymentStatus
     */
    private function mapOrangeStatusToPaymentStatus(string $orangeStatus): string
    {
        return match (strtoupper($orangeStatus)) {
            'SUCCESSFULL', 'SUCCESS' => PaymentStatus::PAID()->value,
            'FAILED' => PaymentStatus::FAILED()->value,
            'EXPIRED' => PaymentStatus::FAILED()->value,
            'INITIATED', 'PENDING' => PaymentStatus::PENDING()->value,
            default => PaymentStatus::PENDING()->value,
        };
    }
}
