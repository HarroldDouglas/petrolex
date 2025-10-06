<?php

namespace App\Http\Controllers\Api\PaymentTest;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentTest\CallbackTestRequest;
use App\Services\PaymentTest\PaymentTestService;
use App\Services\PaymentTest\PaymentTestConstants;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CallbackTestController extends Controller
{
    /**
     * CallbackTestController constructor.
     */
    public function __construct(
        protected PaymentTestService $paymentTestService
    ) {}

    /**
     * Handle payment provider callbacks for testing
     */
    public function handleCallback(CallbackTestRequest $request, string $provider): JsonResponse
    {
        $callbackData = $request->getNormalizedCallbackData($provider);
        $timestamp = now()->toISOString();
        
        Log::info("📞 Test callback received", [
            'provider' => $provider,
            'data' => $callbackData,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        // Validate provider
        if (!PaymentTestConstants::isValidProvider($provider)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid provider for callback',
                'provider' => $provider
            ], 400);
        }

        try {
            // Get status category
            $statusCategory = $request->getStatusCategory();
            
            // Generate logs for frontend display
            $logs = $this->paymentTestService->generateCallbackLogs($provider, $callbackData, $statusCategory);
            
            // Process callback through service
            $result = $this->paymentTestService->processCallback($provider, $callbackData);
            
            // Broadcast callback to test console
            $this->broadcastCallbackToTestConsole([
                'provider' => strtoupper($provider),
                'level' => $statusCategory === PaymentTestConstants::STATUS_SUCCESSFUL ? 'success' : 
                          ($statusCategory === PaymentTestConstants::STATUS_FAILED ? 'error' : 'warning'),
                'message' => $logs[1]['message'] ?? 'Callback processed',
                'details' => $callbackData,
                'timestamp' => $timestamp,
                'isSuccess' => $statusCategory === PaymentTestConstants::STATUS_SUCCESSFUL
            ]);

            return response()->json([
                'success' => true,
                'message' => strtoupper($provider) . ' test callback processed successfully',
                'status' => $callbackData['status'] ?? 'UNKNOWN',
                'category' => $statusCategory,
                'logs' => $logs,
                'timestamp' => $timestamp
            ]);

        } catch (\Exception $e) {
            Log::error("💥 Test callback processing error", [
                'provider' => $provider,
                'error' => $e->getMessage(),
                'data' => $callbackData
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Callback processing failed',
                'error' => $e->getMessage(),
                'logs' => [
                    ['level' => 'error', 'message' => "💥 Critical error: {$e->getMessage()}", 'timestamp' => $timestamp]
                ]
            ], 500);
        }
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