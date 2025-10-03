<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PaymentGateways\MTNMoneyGatewayDraft;
use App\Services\PaymentGateways\OrangeMoneyGatewayDraft;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PaymentTestController extends Controller
{
    /**
     * Process payment test for specified provider
     */
    public function processPayment(Request $request, string $provider): JsonResponse
    {
        $startTime = microtime(true);
        $logContext = [
            'provider' => $provider,
            'phone' => $request->phone_number,
            'amount' => $request->amount,
            'test_mode' => $request->test_mode ?? 'sandbox',
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ];

        // Log the incoming request
        Log::info("🚀 Payment test initiated", $logContext);

        // Validate provider
        if (!in_array($provider, ['mtn', 'orange'])) {
            Log::warning("❌ Invalid provider attempted", array_merge($logContext, ['error' => 'Invalid provider']));
            return response()->json([
                'success' => false,
                'message' => 'Invalid payment provider',
                'provider' => $provider,
                'logs' => [
                    ['level' => 'error', 'message' => "❌ Provider '{$provider}' is not supported", 'timestamp' => now()->toISOString()]
                ]
            ], 400);
        }

        Log::info("✅ Provider validation passed", array_merge($logContext, ['provider' => strtoupper($provider)]));

        // Validate request data
        Log::info("🔍 Validating request data", $logContext);
        
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string|min:9|max:15',
            'amount' => 'required|numeric|min:100|max:1000000',
            'test_mode' => 'sometimes|in:sandbox,live',
            'external_id' => 'sometimes|string|max:50'
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            Log::error("❌ Validation failed", array_merge($logContext, ['validation_errors' => $errors]));
            
            $logs = [];
            foreach ($errors as $field => $messages) {
                foreach ($messages as $message) {
                    $logs[] = ['level' => 'error', 'message' => "❌ {$field}: {$message}", 'timestamp' => now()->toISOString()];
                }
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $errors,
                'provider' => $provider,
                'logs' => $logs
            ], 422);
        }

        Log::info("✅ Request validation passed", $logContext);

        try {
            // Prepare payment data
            $paymentData = [
                'phone_number' => $request->phone_number,
                'amount' => $request->amount,
                'external_id' => $request->external_id ?? $this->generateExternalId($provider),
                'test_mode' => $request->test_mode ?? 'sandbox',
                'reference' => 'Petrolex Test Payment - ' . now()->format('Y-m-d H:i:s')
            ];

            Log::info("📦 Payment data prepared", array_merge($logContext, [
                'external_id' => $paymentData['external_id'],
                'reference' => $paymentData['reference']
            ]));

            // Process payment based on provider
            Log::info("🔄 Initiating {$provider} payment processing", $logContext);
            
            if ($provider === 'mtn') {
                $result = $this->processMTNPayment($paymentData, $logContext);
            } else {
                $result = $this->processOrangePayment($paymentData, $logContext);
            }

            $processingTime = round((microtime(true) - $startTime) * 1000, 2);

            // Log the final result
            if ($result['success']) {
                Log::info("✅ Payment processing completed successfully", array_merge($logContext, [
                    'status' => $result['status'] ?? 'SUCCESS',
                    'processing_time_ms' => $processingTime,
                    'external_id' => $paymentData['external_id']
                ]));
            } else {
                Log::error("❌ Payment processing failed", array_merge($logContext, [
                    'error' => $result['error'] ?? $result['message'],
                    'processing_time_ms' => $processingTime,
                    'external_id' => $paymentData['external_id']
                ]));
            }

            // Add processing logs to response
            $result['logs'] = $this->generateProcessingLogs($provider, $paymentData, $result, $processingTime);
            $result['processing_time_ms'] = $processingTime;

            // Return standardized response
            return response()->json(array_merge($result, [
                'provider' => strtoupper($provider),
                'timestamp' => now()->toISOString(),
                'test_mode' => $paymentData['test_mode']
            ]));

        } catch (\Exception $e) {
            $processingTime = round((microtime(true) - $startTime) * 1000, 2);
            
            Log::error("💥 Critical payment processing error", array_merge($logContext, [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'processing_time_ms' => $processingTime
            ]));

            return response()->json([
                'success' => false,
                'message' => 'Payment processing failed',
                'error' => $e->getMessage(),
                'provider' => strtoupper($provider),
                'timestamp' => now()->toISOString(),
                'processing_time_ms' => $processingTime,
                'logs' => [
                    ['level' => 'error', 'message' => "💥 Critical error: {$e->getMessage()}", 'timestamp' => now()->toISOString()],
                    ['level' => 'info', 'message' => "📊 Processing time: {$processingTime}ms", 'timestamp' => now()->toISOString()]
                ]
            ], 500);
        }
    }

    /**
     * Process MTN Money payment
     */
    protected function processMTNPayment(array $paymentData, array $logContext = []): array
    {
        Log::info("📱 Initializing MTN Money gateway", $logContext);
        $gateway = new MTNMoneyGatewayDraft();
        
        // For testing, use simulation (local testing only)
        if ($paymentData['test_mode'] === 'sandbox') {
            Log::info("🧪 Using MTN sandbox simulation", array_merge($logContext, ['mode' => 'sandbox']));
            return $gateway->simulatePayment($paymentData);
        }
        
        // For live mode, call external payment server
        Log::info("🌐 Calling MTN external payment server", array_merge($logContext, ['mode' => 'live', 'server' => 'isogaz.afrik-solutions.com']));
        return $gateway->requestToPay($paymentData);
    }

    /**
     * Process Orange Money payment
     */
    protected function processOrangePayment(array $paymentData, array $logContext = []): array
    {
        Log::info("🍊 Initializing Orange Money gateway", $logContext);
        $gateway = new OrangeMoneyGatewayDraft();
        
        // For testing, use simulation (local testing only)
        if ($paymentData['test_mode'] === 'sandbox') {
            Log::info("🧪 Using Orange sandbox simulation", array_merge($logContext, ['mode' => 'sandbox']));
            return $gateway->simulatePayment($paymentData);
        }
        
        // For live mode, call external payment server
        Log::info("🌐 Calling Orange external payment server", array_merge($logContext, ['mode' => 'live', 'server' => 'isogaz.afrik-solutions.com']));
        return $gateway->processPayment($paymentData);
    }

    /**
     * Generate unique external ID
     */
    protected function generateExternalId(string $provider): string
    {
        return strtoupper($provider) . '_TEST_' . uniqid() . '_' . time();
    }

    /**
     * Generate processing logs for frontend console
     */
    protected function getProcessingLogs(array $paymentData, array $result, float $processingTime): array
    {
        $logs = [
            ['level' => 'info', 'message' => "🚀 Payment initiation started", 'timestamp' => now()->toISOString()],
            ['level' => 'info', 'message' => "📱 Phone: {$paymentData['phone_number']} | 💰 Amount: {$paymentData['amount']} FCFA", 'timestamp' => now()->toISOString()],
            ['level' => 'info', 'message' => "🔧 Mode: {$paymentData['test_mode']} | 🆔 External ID: {$paymentData['external_id']}", 'timestamp' => now()->toISOString()],
        ];

        if ($result['success']) {
            $logs[] = ['level' => 'success', 'message' => "✅ Payment processed successfully", 'timestamp' => now()->toISOString()];
            
            if (isset($result['status'])) {
                $logs[] = ['level' => 'success', 'message' => "📊 Status: {$result['status']}", 'timestamp' => now()->toISOString()];
            }
            
            if (isset($result['reference_id'])) {
                $logs[] = ['level' => 'success', 'message' => "🆔 Reference ID: {$result['reference_id']}", 'timestamp' => now()->toISOString()];
            }
            
            if (isset($result['transaction_id'])) {
                $logs[] = ['level' => 'success', 'message' => "💳 Transaction ID: {$result['transaction_id']}", 'timestamp' => now()->toISOString()];
            }
        } else {
            $logs[] = ['level' => 'error', 'message' => "❌ Payment failed: {$result['message']}", 'timestamp' => now()->toISOString()];
            
            if (isset($result['error'])) {
                $logs[] = ['level' => 'error', 'message' => "🔍 Error details: {$result['error']}", 'timestamp' => now()->toISOString()];
            }
        }

        $logs[] = ['level' => 'info', 'message' => "⏱️ Processing time: {$processingTime}ms", 'timestamp' => now()->toISOString()];

        return $logs;
    }

    /**
     * Get payment status (bonus endpoint for future use)
     */
    public function getPaymentStatus(Request $request, string $provider, string $transactionId): JsonResponse
    {
        try {
            if ($provider === 'mtn') {
                $gateway = new MTNMoneyGatewayDraft();
                $result = $gateway->getTransactionStatus($transactionId);
            } elseif ($provider === 'orange') {
                $gateway = new OrangeMoneyGatewayDraft();
                $result = $gateway->getTransactionStatus($transactionId);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid provider'
                ], 400);
            }

            return response()->json(array_merge($result, [
                'provider' => strtoupper($provider),
                'transaction_id' => $transactionId,
                'timestamp' => now()->toISOString()
            ]));

        } catch (\Exception $e) {
            Log::error("Payment status check error", [
                'provider' => $provider,
                'transaction_id' => $transactionId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Status check failed',
                'error' => $e->getMessage(),
                'provider' => strtoupper($provider)
            ], 500);
        }
    }

    /**
     * Handle payment provider callbacks
     */
    public function handleCallback(Request $request, string $provider): JsonResponse
    {
        $callbackData = $request->all();
        $timestamp = now()->toISOString();
        
        Log::info("📞 Payment callback received", [
            'provider' => $provider,
            'data' => $callbackData,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        try {
            // Process callback based on provider
            if ($provider === 'orange') {
                return $this->handleOrangeCallback($callbackData, $timestamp);
            } elseif ($provider === 'mtn') {
                return $this->handleMTNCallback($callbackData, $timestamp);
            }

            return response()->json([
                'success' => false,
                'message' => 'Invalid provider'
            ], 400);

        } catch (\Exception $e) {
            Log::error("💥 Callback processing error", [
                'provider' => $provider,
                'error' => $e->getMessage(),
                'data' => $callbackData
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Callback processing failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle Orange Money callback
     */
    protected function handleOrangeCallback(array $data, string $timestamp): JsonResponse
    {
        $payToken = $data['payToken'] ?? 'N/A';
        $status = $data['status'] ?? 'UNKNOWN';
        $message = $data['message'] ?? 'No message';
        $txnid = $data['txnid'] ?? 'N/A';

        Log::info("🍊 Orange Money callback processed", [
            'pay_token' => $payToken,
            'status' => $status,
            'transaction_id' => $txnid,
            'message' => $message
        ]);

        // Determine log level and message based on status
        $logLevel = 'info';
        $logMessage = '';
        $isSuccess = false;

        switch (strtoupper($status)) {
            case 'SUCCESS':
            case 'SUCCESSFUL':
            case 'COMPLETED':
                $logLevel = 'success';
                $logMessage = "🎉 Orange Money: Paiement confirmé avec succès!";
                $isSuccess = true;
                break;
            
            case 'FAILED':
            case 'FAILURE':
            case 'ERROR':
                $logLevel = 'error';
                $logMessage = "❌ Orange Money: Paiement échoué - {$message}";
                break;
            
            case 'PENDING':
            case 'PROCESSING':
                $logLevel = 'warning';
                $logMessage = "⏳ Orange Money: Paiement en cours de traitement";
                break;
            
            default:
                $logLevel = 'info';
                $logMessage = "📋 Orange Money: Status - {$status}";
        }

        // Broadcast callback to test console (this would be handled by websockets in production)
        $this->broadcastCallbackToConsole([
            'provider' => 'ORANGE',
            'level' => $logLevel,
            'message' => $logMessage,
            'details' => [
                'payToken' => $payToken,
                'txnid' => $txnid,
                'status' => $status,
                'originalMessage' => $message
            ],
            'timestamp' => $timestamp,
            'isSuccess' => $isSuccess
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Orange Money callback processed successfully',
            'status' => $status,
            'transaction_id' => $txnid
        ]);
    }

    /**
     * Handle MTN MoMo callback
     */
    protected function handleMTNCallback(array $data, string $timestamp): JsonResponse
    {
        // MTN MoMo typically sends different callback format
        $referenceId = $data['referenceId'] ?? $data['reference_id'] ?? 'N/A';
        $status = $data['status'] ?? 'UNKNOWN';
        $financialTransactionId = $data['financialTransactionId'] ?? $data['financial_transaction_id'] ?? 'N/A';
        $reason = $data['reason'] ?? $data['message'] ?? 'No reason provided';

        Log::info("📱 MTN MoMo callback processed", [
            'reference_id' => $referenceId,
            'status' => $status,
            'financial_transaction_id' => $financialTransactionId,
            'reason' => $reason
        ]);

        // Determine log level and message based to status
        $logLevel = 'info';
        $logMessage = '';
        $isSuccess = false;

        switch (strtoupper($status)) {
            case 'SUCCESSFUL':
            case 'SUCCESS':
            case 'COMPLETED':
                $logLevel = 'success';
                $logMessage = "🎉 MTN MoMo: Transaction confirmée avec succès!";
                $isSuccess = true;
                break;
            
            case 'FAILED':
            case 'FAILURE':
                $logLevel = 'error';
                $logMessage = "❌ MTN MoMo: Transaction échouée - {$reason}";
                break;
            
            case 'PENDING':
            case 'ONGOING':
                $logLevel = 'warning';
                $logMessage = "⏳ MTN MoMo: Transaction en attente de confirmation";
                break;
            
            default:
                $logLevel = 'info';
                $logMessage = "📋 MTN MoMo: Status - {$status}";
        }

        // Broadcast callback to test console
        $this->broadcastCallbackToConsole([
            'provider' => 'MTN',
            'level' => $logLevel,
            'message' => $logMessage,
            'details' => [
                'referenceId' => $referenceId,
                'financialTransactionId' => $financialTransactionId,
                'status' => $status,
                'reason' => $reason
            ],
            'timestamp' => $timestamp,
            'isSuccess' => $isSuccess
        ]);

        return response()->json([
            'success' => true,
            'message' => 'MTN MoMo callback processed successfully',
            'status' => $status,
            'reference_id' => $referenceId
        ]);
    }

    /**
     * Broadcast callback result to test console
     * In production, this would use WebSockets, Server-Sent Events, or similar
     */
    protected function broadcastCallbackToConsole(array $callbackData): void
    {
        // For now, we'll log it in a special way that our test interface can pick up
        // In production, you'd use Laravel Echo, Pusher, or similar real-time solution
        
        Log::channel('single')->info("CALLBACK_BROADCAST", $callbackData);
        
        // Store callback in cache for test interface polling
        $callbackKey = "callback_{$callbackData['provider']}_" . time() . '_' . rand(1000, 9999);
        cache()->put($callbackKey, $callbackData, 300); // 5 minutes
        
        // Maintain list of callback keys
        $existingKeys = cache()->get('callback_keys', []);
        $existingKeys[] = $callbackKey;
        
        // Keep only last 20 callbacks
        if (count($existingKeys) > 20) {
            $oldKey = array_shift($existingKeys);
            cache()->forget($oldKey);
        }
        
        cache()->put('callback_keys', $existingKeys, 300);
    }

    /**
     * Get recent callbacks for test console polling
     */
    public function getRecentCallbacks(Request $request): JsonResponse
    {
        try {
            $callbacks = [];
            $cacheKeys = cache()->get('callback_keys', []);
            
            // Get all cached callbacks
            foreach ($cacheKeys as $key) {
                $callback = cache()->get($key);
                if ($callback) {
                    $callbacks[] = $callback;
                }
            }
            
            // Sort by timestamp (newest first)
            usort($callbacks, function($a, $b) {
                return strtotime($b['timestamp']) - strtotime($a['timestamp']);
            });
            
            // Return last 10 callbacks
            $recentCallbacks = array_slice($callbacks, 0, 10);
            
            return response()->json([
                'success' => true,
                'callbacks' => $recentCallbacks,
                'count' => count($recentCallbacks),
                'timestamp' => now()->toISOString()
            ]);
            
        } catch (\Exception $e) {
            Log::error("Error fetching recent callbacks", ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch callbacks',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate processing logs for frontend display
     */
    private function generateProcessingLogs(string $provider, array $paymentData, array $result, float $processingTime): array
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
            $logs[] = ['level' => 'warning', 'message' => "🧪 Running in SANDBOX mode", 'timestamp' => $timestamp];
        } else {
            $logs[] = ['level' => 'info', 'message' => "🌐 Running in LIVE mode", 'timestamp' => $timestamp];
        }

        // Processing logs based on result
        if ($result['success']) {
            $logs[] = ['level' => 'success', 'message' => "✅ Payment processed successfully", 'timestamp' => $timestamp];
            
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
     * Handle MTN Money callback from external server (external route)
     */
    public function handleMTNExternalCallback(Request $request): JsonResponse
    {
        try {
            Log::info('📞 MTN callback received from external server', [
                'payload' => $request->all(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Process the callback data
            $result = $this->processCallbackData('mtn', $request->all());

            return response()->json([
                'success' => true,
                'message' => 'MTN callback processed successfully',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('❌ MTN callback processing failed', [
                'error' => $e->getMessage(),
                'payload' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'MTN callback processing failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle Orange Money callback from external server (external route)
     */
    public function handleOrangeExternalCallback(Request $request): JsonResponse
    {
        try {
            Log::info('📞 Orange callback received from external server', [
                'payload' => $request->all(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Process the callback data
            $result = $this->processCallbackData('orange', $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Orange callback processed successfully',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Orange callback processing failed', [
                'error' => $e->getMessage(),
                'payload' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Orange callback processing failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process callback data from external server
     */
    private function processCallbackData(string $provider, array $callbackData): array
    {
        // Store callback for later retrieval
        $callbackInfo = [
            'provider' => strtoupper($provider),
            'timestamp' => now()->toISOString(),
            'data' => $callbackData,
            'level' => 'info',
            'message' => "External server callback received for {$provider}",
            'details' => [
                'reference_id' => $callbackData['reference_id'] ?? 'N/A',
                'status' => $callbackData['status'] ?? 'N/A',
                'amount' => $callbackData['amount'] ?? 'N/A'
            ]
        ];

        // Store in cache for frontend polling
        $cacheKey = 'payment_callbacks';
        $callbacks = Cache::get($cacheKey, []);
        $callbacks[] = $callbackInfo;
        
        // Keep only last 50 callbacks
        if (count($callbacks) > 50) {
            $callbacks = array_slice($callbacks, -50);
        }
        
        Cache::put($cacheKey, $callbacks, now()->addHours(1));

        Log::info("✅ {$provider} callback processed and stored", $callbackInfo);

        return $callbackInfo;
    }
}