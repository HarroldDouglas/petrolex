<?php

namespace App\Http\Controllers\Api\PaymentTest;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentTest\PaymentTestRequest;
use App\Services\PaymentTest\PaymentTestService;
use App\Services\PaymentTest\PaymentTestConstants;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class PaymentTestController extends Controller
{
    /**
     * PaymentTestController constructor.
     */
    public function __construct(
        protected PaymentTestService $paymentTestService
    ) {}

    /**
     * Process payment test for specified provider
     */
    public function processPayment(PaymentTestRequest $request, string $provider): JsonResponse
    {
        $startTime = microtime(true);
        
        // Get validated data from the request
        $paymentData = [
            'phone_number' => $request->validated('phone_number'),
            'amount' => $request->validated('amount'),
            'external_id' => $request->validated('external_id'),
            'test_mode' => $request->validated('test_mode', PaymentTestConstants::MODE_SANDBOX),
            'reference' => $request->validated('reference') ?? 'Petrolex Test Payment - ' . now()->format('Y-m-d H:i:s')
        ];
        
        $logContext = [
            'provider' => $provider,
            'phone' => $paymentData['phone_number'],
            'amount' => $paymentData['amount'],
            'test_mode' => $paymentData['test_mode'],
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent()
        ];

        Log::info("🚀 Payment test initiated", $logContext);

        if (!PaymentTestConstants::isValidProvider($provider)) {
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

        try {
            Log::info("📦 Payment data prepared", array_merge($logContext, [
                'external_id' => $paymentData['external_id'],
                'reference' => $paymentData['reference']
            ]));

            Log::info("🔄 Initiating {$provider} payment processing", $logContext);
            
            if (!isset($paymentData['external_id']) || empty($paymentData['external_id'])) {
                $paymentData['external_id'] = $this->paymentTestService->generateExternalId($provider);
            }
            
            $result = $this->paymentTestService->processPayment($provider, $paymentData);

            $processingTime = round((microtime(true) - $startTime) * 1000, 2);

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
            $result['logs'] = $this->paymentTestService->generateProcessingLogs($provider, $paymentData, $result, $processingTime);
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

}