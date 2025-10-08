<?php

namespace App\Jobs;

use App\Services\PaymentService;
use App\Services\PaymentTest\Gateways\MTNMoneyTestGateway;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class VerifyMTNPaymentStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 300;

    private string $referenceId;
    private int $attemptCount;
    private PaymentService $paymentService;

    private const MAX_ATTEMPTS = 18;
    private const CHECK_INTERVAL = 10;

    public function __construct(string $referenceId, int $attemptCount = 1, ?PaymentService $paymentService = null)
    {
        $this->referenceId = $referenceId;
        $this->attemptCount = $attemptCount;
        // Allow optional explicit injection (useful for testing) otherwise resolve from container
        $this->paymentService = $paymentService ?? app(\App\Services\PaymentService::class);
    }

    public function handle(): void
    {
        Log::info('🚀 MTN Payment Status Verification Job Started', [
            'reference_id' => $this->referenceId,
            'attempt' => $this->attemptCount,
        ]);

        try {
            $mtnGateway = new MTNMoneyTestGateway('live');
            $result = $mtnGateway->verify($this->referenceId, $this->attemptCount);

            Log::info('📋 MTN Verification Result', [
                'reference_id' => $this->referenceId,
                'attempt' => $this->attemptCount,
                'result' => $result,
            ]);

            $shouldRetry = ($result['should_retry'] ?? false) && ($this->attemptCount < self::MAX_ATTEMPTS);
            $success = $result['success'] ?? false;
            $finalStatus = $result['status'] ?? null;

            if ($shouldRetry) {
                Log::info('⏳ MTN Payment Pending/Retry Needed', [
                    'reference_id' => $this->referenceId,
                    'attempt' => $this->attemptCount,
                    'status' => $finalStatus,
                    'next_attempt_in' => self::CHECK_INTERVAL.' seconds',
                ]);
                $this->scheduleNextAttempt();

                return; // exit early, still in progress
            }

            // Transaction ended (either success or terminal failure) -> invoke callback handling
            $callbackPayload = [
                'transaction_ref' => $result['reference_id'] ?? $this->referenceId,
                'transaction_status' => $finalStatus,
                'transaction_amount' => $result['amount'] ?? null,
            ] + $result;

            try {
                $this->paymentService->handleCallback($this->referenceId, $callbackPayload);
            } catch (\Throwable $t) {
                Log::error('⚠️ PaymentService handleCallback exception during MTN verification termination', [
                    'reference_id' => $this->referenceId,
                    'attempt' => $this->attemptCount,
                    'error' => $t->getMessage(),
                ]);
            }

            if ($success) {
                Log::info('🏁 MTN Verification Completed - Success', [
                    'reference_id' => $this->referenceId,
                    'final_status' => $finalStatus,
                    'attempts' => $result['total_attempts'] ?? $this->attemptCount,
                    'total_time' => $result['total_time_seconds'] ?? 'N/A',
                ]);
            } else {
                Log::error('🏁 MTN Verification Completed - Failed (Terminal)', [
                    'reference_id' => $this->referenceId,
                    'final_status' => $finalStatus,
                    'message' => $result['message'] ?? null,
                    'attempts' => $result['total_attempts'] ?? $this->attemptCount,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('💥 MTN Verification Job Exception', [
                'reference_id' => $this->referenceId,
                'attempt' => $this->attemptCount,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($this->attemptCount < self::MAX_ATTEMPTS) {
                Log::info('🔄 Scheduling Retry After Exception', [
                    'reference_id' => $this->referenceId,
                    'current_attempt' => $this->attemptCount,
                    'next_attempt' => $this->attemptCount + 1,
                ]);
                $this->scheduleNextAttempt();
            } else {
                Log::error('🚫 Max Attempts Reached After Exception', [
                    'reference_id' => $this->referenceId,
                    'max_attempts' => self::MAX_ATTEMPTS,
                    'final_error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function scheduleNextAttempt(): void
    {
        $nextAttempt = $this->attemptCount + 1;

        Log::info('📅 Scheduling Next MTN Payment Status Check', [
            'reference_id' => $this->referenceId,
            'current_attempt' => $this->attemptCount,
            'next_attempt' => $nextAttempt,
            'delay' => self::CHECK_INTERVAL.' seconds',
        ]);

        dispatch((new self($this->referenceId,$nextAttempt, $this->paymentService))
            ->delay(now()->addSeconds(self::CHECK_INTERVAL)));
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('💥 MTN Payment Status Job Failed Completely', [
            'reference_id' => $this->referenceId,
            'attempt' => $this->attemptCount,
            'exception' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
