<?php

namespace App\Http\Controllers\Api\PaymentTest;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CallbackTestController extends Controller
{
    /**
     * Handle payment provider callbacks for testing
     */
    public function handleCallback(Request $request, string $provider): JsonResponse
    {
        $callbackData = $request->all();
        $timestamp = now()->toISOString();
        
        Log::info("📞 Test callback received", [
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
                'message' => 'Invalid provider for callback'
            ], 400);

        } catch (\Exception $e) {
            Log::error("💥 Test callback processing error", [
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
     * Handle Orange Money callback for testing
     */
    protected function handleOrangeCallback(array $data, string $timestamp): JsonResponse
    {
        $payToken = $data['payToken'] ?? 'N/A';
        $status = $data['status'] ?? 'UNKNOWN';
        $message = $data['message'] ?? 'No message';
        $txnid = $data['txnid'] ?? 'N/A';

        Log::info("🍊 Orange Money test callback processed", [
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
                $logMessage = "🎉 Orange Money Test: Paiement confirmé avec succès!";
                $isSuccess = true;
                break;
            
            case 'FAILED':
            case 'FAILURE':
            case 'ERROR':
                $logLevel = 'error';
                $logMessage = "❌ Orange Money Test: Paiement échoué - {$message}";
                break;
            
            case 'PENDING':
            case 'PROCESSING':
                $logLevel = 'warning';
                $logMessage = "⏳ Orange Money Test: Paiement en cours de traitement";
                break;
            
            default:
                $logLevel = 'info';
                $logMessage = "📋 Orange Money Test: Status - {$status}";
        }

        // Broadcast callback to test console
        $this->broadcastCallbackToTestConsole([
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
            'message' => 'Orange Money test callback processed successfully',
            'status' => $status,
            'transaction_id' => $txnid
        ]);
    }

    /**
     * Handle MTN MoMo callback for testing
     */
    protected function handleMTNCallback(array $data, string $timestamp): JsonResponse
    {
        // MTN MoMo typically sends different callback format
        $referenceId = $data['referenceId'] ?? $data['reference_id'] ?? 'N/A';
        $status = $data['status'] ?? 'UNKNOWN';
        $financialTransactionId = $data['financialTransactionId'] ?? $data['financial_transaction_id'] ?? 'N/A';
        $reason = $data['reason'] ?? $data['message'] ?? 'No reason provided';

        Log::info("📱 MTN MoMo test callback processed", [
            'reference_id' => $referenceId,
            'status' => $status,
            'financial_transaction_id' => $financialTransactionId,
            'reason' => $reason
        ]);

        // Determine log level and message based on status
        $logLevel = 'info';
        $logMessage = '';
        $isSuccess = false;

        switch (strtoupper($status)) {
            case 'SUCCESSFUL':
            case 'SUCCESS':
            case 'COMPLETED':
                $logLevel = 'success';
                $logMessage = "🎉 MTN MoMo Test: Transaction confirmée avec succès!";
                $isSuccess = true;
                break;
            
            case 'FAILED':
            case 'FAILURE':
                $logLevel = 'error';
                $logMessage = "❌ MTN MoMo Test: Transaction échouée - {$reason}";
                break;
            
            case 'PENDING':
            case 'ONGOING':
                $logLevel = 'warning';
                $logMessage = "⏳ MTN MoMo Test: Transaction en attente de confirmation";
                break;
            
            default:
                $logLevel = 'info';
                $logMessage = "📋 MTN MoMo Test: Status - {$status}";
        }

        // Broadcast callback to test console
        $this->broadcastCallbackToTestConsole([
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
            'message' => 'MTN MoMo test callback processed successfully',
            'status' => $status,
            'reference_id' => $referenceId
        ]);
    }

    /**
     * Handle MTN Money callback from external server (test environment)
     */
    public function handleMTNExternalCallback(Request $request): JsonResponse
    {
        try {
            Log::info('📞 MTN external test callback received', [
                'payload' => $request->all(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Process the callback data
            $result = $this->processExternalCallbackData('mtn', $request->all());

            return response()->json([
                'success' => true,
                'message' => 'MTN external test callback processed successfully',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('❌ MTN external test callback processing failed', [
                'error' => $e->getMessage(),
                'payload' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'MTN external test callback processing failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle Orange Money callback from external server (test environment)
     */
    public function handleOrangeExternalCallback(Request $request): JsonResponse
    {
        try {
            Log::info('📞 Orange external test callback received', [
                'payload' => $request->all(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Process the callback data
            $result = $this->processExternalCallbackData('orange', $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Orange external test callback processed successfully',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Orange external test callback processing failed', [
                'error' => $e->getMessage(),
                'payload' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Orange external test callback processing failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Broadcast callback result to test console
     */
    protected function broadcastCallbackToTestConsole(array $callbackData): void
    {
        // Log it in a special way that the test interface can pick up
        Log::channel('single')->info("TEST_CALLBACK_BROADCAST", $callbackData);
        
        // Store callback in cache for test interface polling
        $callbackKey = "test_callback_{$callbackData['provider']}_" . time() . '_' . rand(1000, 9999);
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

        Log::info("✅ Test callback broadcasted to console", [
            'provider' => $callbackData['provider'],
            'cache_key' => $callbackKey
        ]);
    }

    /**
     * Process callback data from external server (test environment)
     */
    private function processExternalCallbackData(string $provider, array $callbackData): array
    {
        // Store callback for later retrieval
        $callbackInfo = [
            'provider' => strtoupper($provider),
            'timestamp' => now()->toISOString(),
            'data' => $callbackData,
            'level' => 'info',
            'message' => "External test server callback received for {$provider}",
            'details' => [
                'reference_id' => $callbackData['reference_id'] ?? 'N/A',
                'status' => $callbackData['status'] ?? 'N/A',
                'amount' => $callbackData['amount'] ?? 'N/A'
            ],
            'test_environment' => true
        ];

        // Store in cache for frontend polling
        $cacheKey = 'test_payment_callbacks';
        $callbacks = Cache::get($cacheKey, []);
        $callbacks[] = $callbackInfo;
        
        // Keep only last 50 callbacks
        if (count($callbacks) > 50) {
            $callbacks = array_slice($callbacks, -50);
        }
        
        Cache::put($cacheKey, $callbacks, now()->addHours(1));

        Log::info("✅ Test {$provider} external callback processed and stored", $callbackInfo);

        return $callbackInfo;
    }
}