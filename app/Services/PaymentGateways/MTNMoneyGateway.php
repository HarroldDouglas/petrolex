<?php

namespace App\Services\PaymentGateways;

use App\Contracts\PaymentGateway;
use App\DTOs\PaymentCallbackData;
use App\DTOs\PaymentDetailsData;
use App\DTOs\PaymentResponse;
use App\Enums\PaymentStatus;
use App\Models\OrderPayment;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MTNMoneyGateway implements PaymentGateway
{
    protected array $config;
    protected ?string $accessToken = null;

    public function __construct()
    {
        $this->config = config('mtnmoney', []);

        if (empty($this->config)) {
            log::warning('⚠️ MTNMoneyTestGateway: mtnmoney config is empty!');
        }

        if (! isset($this->config['base_url']) || empty($this->config['base_url'])) {
            throw new \Exception('MTN MoMo base URL is not configured. Please check MTN_MOMO_BASE_URL environment variable.');
        }

        Log::info('MTN MoMo Gateway initialized', [
            'environment' => $this->config['target_environment'],
            'base_url' => $this->config['base_url'],
            'config_loaded' => true,
            'config_source' => 'laravel_config',
        ]);
    }

    public function getAccessToken(string $product = 'collection'): string
    {
        try {
            $endpoint = $this->config['endpoints'][$product]['token'];

            Log::info('MTN MoMo: Requesting access token', [
                'product' => $product,
                'endpoint' => $endpoint,
                'environment' => $this->config['target_environment'],
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Basic '.base64_encode($this->config['api_user'].':'.$this->config['api_key']),
                'Ocp-Apim-Subscription-Key' => $this->config['subscription_key'],
                'X-Target-Environment' => $this->config['target_environment'],
            ])->post($this->config['base_url'].$endpoint);

            if ($response->successful()) {
                $data = $response->json();
                $this->accessToken = $data['access_token'];

                Log::info('MTN MoMo: Access token obtained successfully', [
                    'product' => $product,
                    'token_length' => strlen($this->accessToken),
                ]);

                return $this->accessToken;
            }

            throw new Exception('Failed to get access token: '.$response->body());
        } catch (Exception $e) {
            Log::error('MTN MoMo Token Error: '.$e->getMessage(), [
                'product' => $product,
                'environment' => $this->config['target_environment'],
            ]);
            throw $e;
        }
    }

    public function initiatePayment(OrderPayment $payment, PaymentDetailsData $paymentDetails): PaymentResponse
    {
        try {
            if (! $this->accessToken) {
                $this->getAccessToken('collection');
            }

            $referenceId = Str::uuid()->toString();
            $endpoint = $this->config['endpoints']['collection']['request_to_pay'];

            // Get phone number from payment details (custom request) or fallback to order customer's user
            $phoneNumber = $paymentDetails->getPhone() ?? $payment->order->customer->user->phone_number ?? null;
            if (empty($phoneNumber)) {
                Log::error('MTN MoMo Request Error: Phone number is required for MTN Money payment', [
                    'payment_id' => $payment->id,
                    'order_id' => $payment->order_id,
                    'customer_id' => $payment->order->customer_id,
                    'user_id' => $payment->order->customer->user_id ?? null,
                    'user_phone' => $payment->order->customer->user->phone_number ?? 'not_loaded',
                    'payment_details_phone' => $paymentDetails->getPhone() ?? 'not_provided',
                ]);
                throw new \InvalidArgumentException('Phone number is required for MTN Money payment');
            }

            $formattedPhone = $this->formatPhoneNumber($phoneNumber);
            $amount = (string) (int) $payment->amount_due;

            Log::info('MTN MoMo: Phone number source', [
                'phone_from_payment_details' => $paymentDetails->getPhone(),
                'phone_from_customer_user' => $payment->order->customer->user->phone_number ?? null,
                'selected_phone' => $phoneNumber,
                'formatted_phone' => $formattedPhone,
                'is_mobile_payment' => $paymentDetails->isMobilePayment(),
            ]);
            $requestData = [
                'amount' => (string) $amount,
                'currency' => config('payment.defaults.currency', 'XAF'),
                'externalId' => $payment->payment_reference,
                'payer' => [
                    'partyIdType' => 'MSISDN',
                    'partyId' => $formattedPhone,
                ],
                'payerMessage' => $payment->payment_reference ?? 'Test Payment',
                'payeeNote' => 'MTN MoMo Payment via Petrolex',
            ];

            Log::info('MTN MoMo: API configuration', [
                'base_url' => $this->config['base_url'],
                'endpoint' => $endpoint,
                'full_url' => $this->config['base_url'].$endpoint,
                'reference_id' => $referenceId,
                'enviroenment' => $this->config['target_environment'],
            ]);

            Log::info('MTN MoMo: Calling MTN API', [
                'api_url' => $this->config['base_url'].$endpoint,
                'reference_id' => $referenceId,
                'external_id' => $payment->payment_reference,
                'amount' => $amount,
                'phone' => $formattedPhone,
                'environment' => $this->config['target_environment'],
            ]);

            $url = $this->config['base_url'].$endpoint;

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->accessToken,
                'X-Reference-Id' => $referenceId,
                'X-Target-Environment' => $this->config['target_environment'],
                'Ocp-Apim-Subscription-Key' => $this->config['subscription_key'],
                'Content-Type' => 'application/json',
            ])->post($url, $requestData);

            if ($response->status() === 202) {
                Log::info('MTN MoMo: Payment request sent successfully to MTN API', [
                    'response' => $response,
                    'response_status' => $response->status(),
                    'reference_id' => $referenceId,
                    'external_id' => $payment->payment_reference,
                    'environment' => $this->config['target_environment'],
                ]);
                Log::info('📅 MTN Payment Status Verification Job Dispatched', [
                    'reference_id' => $referenceId,
                    'external_id' => $payment->payment_reference,
                    'verification_schedule' => 'Every 10 seconds for 3 minutes (max 18 attempts)',
                    'attempt_count' => 1,
                ]);

                return new PaymentResponse(
                    success: true,
                    status: PaymentStatus::PENDING()->value,
                    transactionReference: $referenceId,
                    paymentUrl: $url,
                    amount: $payment->amount_due,
                    errorMessage: null,
                    gatewayResponse: $response->json()
                );
            }

            throw new Exception('MTN API request failed with status: '.$response->status().' - '.$response->body());
        } catch (Exception $e) {
            Log::error('MTN MoMo Request Error: '.$e->getMessage(), [
                'payment_data' => $payment,
                'environment' => $this->config['target_environment'],
            ]);

            return new PaymentResponse(
                success: false,
                status: PaymentStatus::FAILED()->value,
                transactionReference: null,
                paymentUrl: null,
                amount: $payment->amount_due ?? null,
                errorMessage: 'Payment request failed: '.$e->getMessage(),
                gatewayResponse: null
            );
        }
    }

    public function handleCallback(PaymentCallbackData $callbackData): PaymentResponse
    {
        try {
            Log::info('MTN Money callback received', [
                'transaction_ref' => $callbackData->transactionReference,
                'status' => $callbackData->status,
            ]);

            $status = $callbackData->status;

            return new PaymentResponse(
                success: true,
                status: $status,
                transactionReference: $callbackData->transactionReference,
                gatewayResponse: $callbackData->rawData ?? []
            );

        } catch (\Exception $e) {
            Log::error('MTN Money callback processing failed', [
                'error' => $e->getMessage(),
                'callback_data' => $callbackData,
            ]);

            return new PaymentResponse(
                success: false,
                status: PaymentStatus::FAILED()->value,
                transactionReference: $callbackData->transactionReference,
                paymentUrl: null,
                amount: null,
                errorMessage: 'Failed to process MTN Money callback: '.$e->getMessage(),
                gatewayResponse: null
            );
        }
    }

    /**
     * Map MTN Money status to internal status
     */
    private function mapMTNMoneyStatus(string $mtnStatus): string
    {
        $statusMappings = [
            'SUCCESSFUL' => 'SUCCESS',
            'PENDING' => 'PENDING',
            'FAILED' => 'FAILED',
            'TIMEOUT' => 'FAILED',
            'CANCELLED' => 'FAILED',
            'EXPIRED' => 'FAILED',
        ];

        return $statusMappings[$mtnStatus] ?? 'FAILED';
    }

    /**
     * Get user-friendly status message
     */
    private function getStatusMessage(string $status): string
    {
        $messages = [
            'SUCCESS' => 'Payment completed successfully',
            'PENDING' => 'Payment is still being processed',
            'FAILED' => 'Payment failed or was cancelled',
        ];

        return $messages[$status] ?? 'Payment status unknown';
    }

    public function verifyPayment(string $transactionReference): PaymentResponse
    {
        try {
            if (! $this->accessToken) {
                $this->getAccessToken('collection');
            }

            $endpoint = str_replace('{referenceId}', $transactionReference, $this->config['endpoints']['collection']['transaction_status']);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->accessToken,
                'X-Target-Environment' => $this->config['target_environment'],
                'Ocp-Apim-Subscription-Key' => $this->config['subscription_key'],
            ])->get($this->config['base_url'].$endpoint);

            Log::info('NEW MTN MoMo: Checking transaction status', [
                'response' => $response,
                'reference_id' => $transactionReference,
                'endpoint' => $endpoint,
                'full_url' => $this->config['base_url'].$endpoint,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                Log::info('Payment: Detailed transaction status', [
                    'reference_id' => $transactionReference,
                    'status' => $data['status'] ?? 'UNKNOWN',
                    'reason' => $data['reason'] ?? 'No reason provided',
                    'financial_transaction_id' => $data['financialTransactionId'] ?? null,
                    'full_response' => $data,
                ]);

                return new PaymentResponse(
                    success: true,
                    status: $data['status'] ?? 'UNKNOWN',
                    transactionReference: $transactionReference,
                    paymentUrl: null,
                    amount: (float) ($data['amount'] ?? 0),
                    errorMessage: $this->mapMTNFailureReason($data['reason'] ?? null),
                    gatewayResponse: $data
                );
            }

            throw new Exception('Status check failed: '.$response->body());
        } catch (Exception $e) {
            Log::error('MTN MoMo Status Check Error: '.$e->getMessage());

            return new PaymentResponse(
                success: false,
                status: 'FAILED',
                transactionReference: $transactionReference,
                paymentUrl: null,
                amount: null,
                errorMessage: 'Status check failed: '.$e->getMessage(),
                gatewayResponse: null
            );
        }
    }

    private function mapMTNFailureReason(?string $reason): ?string
    {
        if (! $reason) {
            return null;
        }

        return match ($reason) {
            'LOW_BALANCE_OR_PAYEE_LIMIT_REACHED_OR_NOT_ALLOWED' => 'Solde insuffisant ou limite de paiement atteinte.',
            'PAYER_NOT_FOUND'                                    => 'Numéro non enregistré sur MTN Mobile Money.',
            'NOT_ALLOWED'                                        => 'Transaction non autorisée par MTN.',
            'NOT_ALLOWED_TARGET_ENVIRONMENT'                     => 'Transaction non autorisée dans cet environnement.',
            'APPROVAL_REJECTED'                                  => 'Paiement refusé par le client.',
            'EXPIRED'                                            => 'Délai de confirmation expiré.',
            'CANCELLED'                                          => 'Paiement annulé.',
            'RESOURCE_NOT_FOUND'                                 => 'Transaction introuvable.',
            default                                              => "Échec du paiement MTN ({$reason}).",
        };
    }

    public function supports(string $paymentMethod): bool
    {
        return $paymentMethod === 'mtn_money';
    }

    protected function formatPhoneNumber(?string $phoneNumber): string
    {
        if (empty($phoneNumber)) {
            throw new \InvalidArgumentException('Phone number cannot be empty');
        }

        $cleaned = preg_replace('/\D/', '', $phoneNumber);

        if (str_starts_with($cleaned, '237')) {
            return $cleaned;
        }

        if (str_starts_with($cleaned, '6')) {
            return '237'.$cleaned;
        }

        return '237'.$cleaned;
    }
}
