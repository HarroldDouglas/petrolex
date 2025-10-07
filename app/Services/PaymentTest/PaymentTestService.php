<?php

namespace App\Services\PaymentTest;

use App\Services\PaymentTest\Gateways\MTNMoneyTestGateway;
use App\Services\PaymentTest\Gateways\OrangeMoneyTestGateway;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Payment Test Service
 *
 * Central service for handling payment test operations.
 * Manages gateway selection and payment processing.
 */
class PaymentTestService
{
    /**
     * Process payment using appropriate gateway
     */
    public function processPayment(string $provider, array $paymentData, array $logContext = []): array
    {
        try {
            Log::info("🔄 PaymentTestService: Processing {$provider} payment", array_merge($logContext, [
                'service' => 'PaymentTestService',
                'method' => 'processPayment',
            ]));

            $gateway = $this->createGateway($provider, $paymentData['test_mode']);

            if ($paymentData['test_mode'] === 'sandbox') {
                Log::info('🧪 PaymentTestService: Using sandbox simulation', array_merge($logContext, [
                    'mode' => 'sandbox',
                    'provider' => $provider,
                ]));

                return $gateway->simulatePayment($paymentData);
            }

            Log::info('🌐 PaymentTestService: Using live gateway', array_merge($logContext, [
                'mode' => 'live',
                'provider' => $provider,
                'server' => 'isogaz.afrik-solutions.com',
            ]));

            return $gateway->initPayment($paymentData);

        } catch (Exception $e) {
            Log::error('💥 Payment processing failed', array_merge($logContext, [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]));

            return [
                'success' => false,
                'message' => 'Payment processing failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate unique external ID
     */
    public function generateExternalId(string $provider): string
    {
        return strtoupper($provider).'_TEST_'.uniqid().'_'.time();
    }

    /**
     * Generate processing logs for frontend display
     */
    public function generateProcessingLogs(string $provider, array $paymentData, array $result, float $processingTime): array
    {
        $logs = [];
        $timestamp = now()->toISOString();
        $providerName = strtoupper($provider);

        // Initial logs
        $logs[] = ['level' => 'info', 'message' => "🚀 {$providerName} payment initiated", 'timestamp' => $timestamp];
        $logs[] = ['level' => 'info', 'message' => "📱 Phone: {$paymentData['phone_number']}", 'timestamp' => $timestamp];
        $logs[] = ['level' => 'info', 'message' => "💰 Amount: {$paymentData['amount']} FCFA", 'timestamp' => $timestamp];
        $logs[] = ['level' => 'info', 'message' => "🔧 Mode: {$paymentData['test_mode']}", 'timestamp' => $timestamp];

        if (isset($paymentData['external_id'])) {
            $logs[] = ['level' => 'info', 'message' => "🆔 Transaction ID: {$paymentData['external_id']}", 'timestamp' => $timestamp];
        }

        // Gateway initialization
        $logs[] = ['level' => 'info', 'message' => "📱 Initializing {$providerName} gateway", 'timestamp' => $timestamp];

        // Mode-specific logs
        if ($paymentData['test_mode'] === 'sandbox') {
            $logs[] = ['level' => 'warning', 'message' => '🧪 Running in SANDBOX mode', 'timestamp' => $timestamp];
        } else {
            $logs[] = ['level' => 'info', 'message' => '🌐 Running in LIVE mode', 'timestamp' => $timestamp];
        }

        // Processing logs based on result
        if ($result['success']) {
            $logs[] = ['level' => 'success', 'message' => '✅ Payment processed successfully', 'timestamp' => $timestamp];

            if (isset($result['reference_id'])) {
                $logs[] = ['level' => 'success', 'message' => "🔗 Reference ID: {$result['reference_id']}", 'timestamp' => $timestamp];
            }

            if (isset($result['financial_transaction_id'])) {
                $logs[] = ['level' => 'success', 'message' => "🏦 Financial Transaction ID: {$result['financial_transaction_id']}", 'timestamp' => $timestamp];
            }

            $status = $result['status'] ?? 'COMPLETED';
            $logs[] = ['level' => 'success', 'message' => "📊 Status: {$status}", 'timestamp' => $timestamp];

        } else {
            $error = $result['error'] ?? $result['message'] ?? 'Unknown error';
            $logs[] = ['level' => 'error', 'message' => "❌ Payment failed: {$error}", 'timestamp' => $timestamp];

            if (isset($result['error_code'])) {
                $logs[] = ['level' => 'error', 'message' => "🔢 Error Code: {$result['error_code']}", 'timestamp' => $timestamp];
            }
        }

        // Processing time
        $logs[] = ['level' => 'info', 'message' => "⏱️ Processing time: {$processingTime}ms", 'timestamp' => $timestamp];

        return $logs;
    }

    /**
     * Process callback using appropriate gateway
     */
    public function processCallback(string $provider, array $callbackData): array
    {
        try {
            Log::info("📞 PaymentTestService: Processing {$provider} callback", [
                'service' => 'PaymentTestService',
                'method' => 'processCallback',
                'provider' => $provider,
                'data_keys' => array_keys($callbackData),
            ]);

            $gateway = $this->createGateway($provider, 'live');

            return $gateway->handleCallback($callbackData);

        } catch (Exception $e) {
            Log::error('💥 Callback processing failed', [
                'provider' => $provider,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'callback_data' => $callbackData,
            ]);

            return [
                'success' => false,
                'message' => 'Callback processing failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate callback logs for frontend display
     */
    public function generateCallbackLogs(string $provider, array $callbackData, string $statusCategory): array
    {
        $logs = [];
        $timestamp = now()->toISOString();
        $providerName = $this->getProviderDisplayName($provider);

        // Initial log
        $logs[] = ['level' => 'info', 'message' => "📞 {$providerName} callback received", 'timestamp' => $timestamp];

        // Status-based logs
        switch ($statusCategory) {
            case 'success':
                $logs[] = ['level' => 'success', 'message' => "🎉 {$providerName}: Transaction confirmée avec succès!", 'timestamp' => $timestamp];
                break;
            case 'failed':
                $reason = $callbackData['reason'] ?? $callbackData['message'] ?? 'Unknown error';
                $logs[] = ['level' => 'error', 'message' => "❌ {$providerName}: Transaction échouée - {$reason}", 'timestamp' => $timestamp];
                break;
            case 'pending':
                $logs[] = ['level' => 'warning', 'message' => "⏳ {$providerName}: Transaction en cours de traitement", 'timestamp' => $timestamp];
                break;
            default:
                $status = $callbackData['status'] ?? 'UNKNOWN';
                $logs[] = ['level' => 'info', 'message' => "📋 {$providerName}: Status - {$status}", 'timestamp' => $timestamp];
        }

        // Add transaction details
        if ($provider === 'mtn') {
            if (isset($callbackData['reference_id'])) {
                $logs[] = ['level' => 'info', 'message' => "🔗 Reference ID: {$callbackData['reference_id']}", 'timestamp' => $timestamp];
            }
            if (isset($callbackData['financial_transaction_id'])) {
                $logs[] = ['level' => 'info', 'message' => "🏦 Financial Transaction ID: {$callbackData['financial_transaction_id']}", 'timestamp' => $timestamp];
            }
        } elseif ($provider === 'orange') {
            if (isset($callbackData['pay_token'])) {
                $logs[] = ['level' => 'info', 'message' => "🎫 Pay Token: {$callbackData['pay_token']}", 'timestamp' => $timestamp];
            }
            if (isset($callbackData['transaction_id'])) {
                $logs[] = ['level' => 'info', 'message' => "🆔 Transaction ID: {$callbackData['transaction_id']}", 'timestamp' => $timestamp];
            }
        }

        return $logs;
    }

    /**
     * Create gateway instance for provider
     */
    protected function createGateway(string $provider, string $testMode): MTNMoneyTestGateway|OrangeMoneyTestGateway
    {
        return match ($provider) {
            'mtn' => new MTNMoneyTestGateway($testMode),
            'orange' => new OrangeMoneyTestGateway($testMode),
            default => throw new Exception("Unsupported payment provider: {$provider}")
        };
    }

    /**
     * Get provider display name from config
     */
    private function getProviderDisplayName(string $provider): string
    {
        return config("payment.providers.{$provider}.name", strtoupper($provider));
    }
}
