<?php

namespace App\Http\Controllers\Api\PaymentTest;

use App\Http\Controllers\Controller;
use App\Services\PaymentTest\Gateways\MTNMoneyTestGateway;
use App\Services\PaymentTest\Gateways\OrangeMoneyTestGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class StatusTestController extends Controller
{
    /**
     * Get payment status for testing
     */
    public function getPaymentStatus(Request $request, string $provider, string $transactionId): JsonResponse
    {
        try {

            if ($provider === 'mtn') {
                $gateway = new MTNMoneyTestGateway('live'); // Default to live for status checks
                $result = $gateway->getTransactionStatus($transactionId);
            } elseif ($provider === 'orange') {
                $gateway = new OrangeMoneyTestGateway('live'); // Default to live for status checks
                $result = $gateway->getTransactionStatus($transactionId);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid provider for status check',
                ], 400);
            }

            Log::info('✅ Payment status retrieved successfully', [
                'provider' => $provider,
                'transaction_id' => $transactionId,
                'status' => $result['status'] ?? 'UNKNOWN',
            ]);

            return response()->json(array_merge($result, [
                'provider' => strtoupper($provider),
                'transaction_id' => $transactionId,
                'timestamp' => now()->toISOString(),
            ]));

        } catch (\Exception $e) {
            Log::error('❌ Payment status check failed', [
                'provider' => $provider,
                'transaction_id' => $transactionId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Status check failed',
                'error' => $e->getMessage(),
                'provider' => strtoupper($provider),
            ], 500);
        }
    }

    /**
     * Get recent callbacks for test console polling
     */
    public function getRecentCallbacks(Request $request): JsonResponse
    {
        try {
            $callbacks = [];
            $cacheKeys = cache()->get('callback_keys', []);

            foreach ($cacheKeys as $key) {
                $callback = cache()->get($key);
                if ($callback) {
                    $callbacks[] = $callback;
                }
            }

            usort($callbacks, function ($a, $b) {
                return strtotime($b['timestamp']) - strtotime($a['timestamp']);
            });

            $recentCallbacks = array_slice($callbacks, 0, 10);

            return response()->json([
                'success' => true,
                'callbacks' => $recentCallbacks,
                'count' => count($recentCallbacks),
                'timestamp' => now()->toISOString(),
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Error fetching recent callbacks', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch callbacks',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get transaction history for testing dashboard
     */
    public function getTransactionHistory(Request $request): JsonResponse
    {
        try {
            $transactions = Cache::get('test_transactions', []);

            if ($request->has('provider')) {
                $provider = strtoupper($request->provider);
                $transactions = array_filter($transactions, function ($transaction) use ($provider) {
                    return $transaction['provider'] === $provider;
                });
            }

            usort($transactions, function ($a, $b) {
                return strtotime($b['timestamp']) - strtotime($a['timestamp']);
            });

            $limit = min($request->get('limit', 20), 50);
            $transactions = array_slice($transactions, 0, $limit);

            return response()->json([
                'success' => true,
                'transactions' => $transactions,
                'count' => count($transactions),
                'timestamp' => now()->toISOString(),
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Error fetching transaction history', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch transaction history',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store test transaction for history tracking
     */
    public function storeTestTransaction(array $transactionData): void
    {
        try {
            $transactions = Cache::get('test_transactions', []);

            $transactions[] = [
                'id' => $transactionData['external_id'] ?? uniqid(),
                'provider' => strtoupper($transactionData['provider'] ?? 'UNKNOWN'),
                'phone' => $transactionData['phone_number'] ?? 'N/A',
                'amount' => $transactionData['amount'] ?? 0,
                'status' => $transactionData['status'] ?? 'UNKNOWN',
                'reference_id' => $transactionData['reference_id'] ?? null,
                'external_id' => $transactionData['external_id'] ?? null,
                'test_mode' => $transactionData['test_mode'] ?? 'sandbox',
                'timestamp' => now()->toISOString(),
                'processing_time_ms' => $transactionData['processing_time_ms'] ?? null,
            ];

            if (count($transactions) > 100) {
                $transactions = array_slice($transactions, -100);
            }

            Cache::put('test_transactions', $transactions, now()->addDays(7));

        } catch (\Exception $e) {
            Log::error('❌ Error storing test transaction', [
                'error' => $e->getMessage(),
                'transaction_data' => $transactionData,
            ]);
        }
    }
}
