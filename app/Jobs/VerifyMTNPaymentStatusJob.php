<?php

namespace App\Jobs;

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

    private string $referenceId;
    private int $attemptCount;
    private const MAX_ATTEMPTS = 18; // 3 minutes / 10 seconds = 18 attempts
    private const CHECK_INTERVAL = 10; // 10 seconds

    public function __construct(string $referenceId, int $attemptCount = 1)
    {
        $this->referenceId = $referenceId;
        $this->attemptCount = $attemptCount;
        
        // Delay the job execution by 10 seconds if it's not the first attempt
        if ($attemptCount > 1) {
            $this->delay(now()->addSeconds(self::CHECK_INTERVAL));
        }
    }

    public function handle(): void
    {
        Log::info('🔍 MTN Payment Status Verification Started', [
            'reference_id' => $this->referenceId,
            'attempt' => $this->attemptCount,
            'max_attempts' => self::MAX_ATTEMPTS,
        ]);

        try {
            // Get the payment transaction details
            $transactionId = $this->getTransactionId();
            if (!$transactionId) {
                Log::error('❌ MTN Payment Status Check Failed: Transaction ID not found', [
                    'payment_id' => $this->referenceId,
                ]);
                return;
            }

            // Initialize MTN gateway
            $mtnGateway = new MTNMoneyTestGateway();
            
            // Check payment status
            $statusResponse = $mtnGateway->getTransactionStatus($transactionId);
            
            Log::info('📊 MTN Status Check Response', [
                'payment_id' => $this->referenceId,
                'attempt' => $this->attemptCount,
                'transaction_id' => $transactionId,
                'response' => $statusResponse,
            ]);

            if (!$statusResponse['success']) {
                Log::warning('⚠️ MTN Status Check API Failed', [
                    'payment_id' => $this->referenceId,
                    'attempt' => $this->attemptCount,
                    'error' => $statusResponse['error'] ?? 'Unknown error',
                ]);

                // Retry if not at max attempts
                if ($this->attemptCount < self::MAX_ATTEMPTS) {
                    $this->scheduleNextAttempt();
                } else {
                    $this->handleMaxAttemptsReached('API_ERROR');
                }
                return;
            }

            $currentStatus = strtoupper($statusResponse['status'] ?? 'UNKNOWN');
            
            // Check if status is successful
            if ($this->isSuccessfulStatus($currentStatus)) {
                $this->handleSuccessfulPayment($currentStatus, $statusResponse);
                return;
            }

            // Check if status is failed
            if ($this->isFailedStatus($currentStatus)) {
                $this->handleFailedPayment($currentStatus, $statusResponse);
                return;
            }

            // Status is still pending
            if ($this->isPendingStatus($currentStatus)) {
                // Continue polling if not at max attempts
                if ($this->attemptCount < self::MAX_ATTEMPTS) {
                    Log::info('⏳ MTN Payment Still Pending, Scheduling Next Check', [
                        'payment_id' => $this->referenceId,
                        'attempt' => $this->attemptCount,
                        'status' => $currentStatus,
                        'next_check_in' => self::CHECK_INTERVAL . ' seconds',
                    ]);
                    
                    $this->scheduleNextAttempt();
                } else {
                    $this->handleMaxAttemptsReached('TIMEOUT_PENDING');
                }
                return;
            }

            // Unknown status - treat as pending
            Log::warning('⚠️ Unknown MTN Payment Status', [
                'payment_id' => $this->referenceId,
                'attempt' => $this->attemptCount,
                'status' => $currentStatus,
            ]);

            if ($this->attemptCount < self::MAX_ATTEMPTS) {
                $this->scheduleNextAttempt();
            } else {
                $this->handleMaxAttemptsReached('UNKNOWN_STATUS');
            }

        } catch (\Exception $e) {
            Log::error('❌ MTN Payment Status Verification Exception', [
                'payment_id' => $this->referenceId,
                'attempt' => $this->attemptCount,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Retry on exception if not at max attempts
            if ($this->attemptCount < self::MAX_ATTEMPTS) {
                $this->scheduleNextAttempt();
            } else {
                $this->handleMaxAttemptsReached('EXCEPTION');
            }
        }
    }

    private function getTransactionId(): ?string
    {
        // The referenceId passed to the job is already the MTN reference ID
        // that should be used for status checking
        return $this->referenceId;
    }

    private function isSuccessfulStatus(string $status): bool
    {
        $successStatuses = config('payment.status_mappings.success_statuses', []);
        return in_array($status, $successStatuses);
    }

    private function isFailedStatus(string $status): bool
    {
        $failedStatuses = config('payment.status_mappings.failed_statuses', []);
        return in_array($status, $failedStatuses);
    }

    private function isPendingStatus(string $status): bool
    {
        $pendingStatuses = config('payment.status_mappings.pending_statuses', []);
        return in_array($status, $pendingStatuses);
    }

    private function scheduleNextAttempt(): void
    {
        $nextAttempt = $this->attemptCount + 1;
        
        Log::info('📅 Scheduling Next MTN Payment Status Check', [
            'payment_id' => $this->referenceId,
            'current_attempt' => $this->attemptCount,
            'next_attempt' => $nextAttempt,
            'delay' => self::CHECK_INTERVAL . ' seconds',
        ]);

        // Dispatch the next attempt
        dispatch(new self($this->referenceId, $nextAttempt));
    }

    private function handleSuccessfulPayment(string $status, array $response): void
    {
        $totalTime = $this->attemptCount * self::CHECK_INTERVAL;
        
        Log::info('✅ MTN Payment Verification Successful', [
            'payment_id' => $this->referenceId,
            'final_status' => $status,
            'total_attempts' => $this->attemptCount,
            'total_time_seconds' => $totalTime,
            'response' => $response,
        ]);

        // TODO: Update payment record in database when requirement is ready
        // $this->updatePaymentStatus($status, 'success', $response);
    }

    private function handleFailedPayment(string $status, array $response): void
    {
        $totalTime = $this->attemptCount * self::CHECK_INTERVAL;
        
        Log::error('❌ MTN Payment Verification Failed', [
            'payment_id' => $this->referenceId,
            'final_status' => $status,
            'total_attempts' => $this->attemptCount,
            'total_time_seconds' => $totalTime,
            'response' => $response,
        ]);

        // TODO: Update payment record in database when requirement is ready
        // $this->updatePaymentStatus($status, 'failed', $response);
    }

    private function handleMaxAttemptsReached(string $reason): void
    {
        $totalTime = self::MAX_ATTEMPTS * self::CHECK_INTERVAL;
        
        Log::error('⏰ MTN Payment Verification Timeout', [
            'payment_id' => $this->referenceId,
            'reason' => $reason,
            'max_attempts_reached' => self::MAX_ATTEMPTS,
            'total_time_seconds' => $totalTime,
            'total_time_minutes' => $totalTime / 60,
        ]);

        // Consider payment as failed after timeout
        // TODO: Update payment record in database when requirement is ready
        // $this->updatePaymentStatus('FAILED', 'timeout', ['reason' => $reason]);
    }

    // TODO: Implement when database update requirement is ready
    // private function updatePaymentStatus(string $status, string $result, array $data): void
    // {
    //     // Update payment record with final status
    //     // This will be implemented when database updates are required
    // }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('💥 MTN Payment Status Job Failed Completely', [
            'payment_id' => $this->referenceId,
            'attempt' => $this->attemptCount,
            'exception' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}