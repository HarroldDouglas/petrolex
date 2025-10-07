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
    private const MAX_ATTEMPTS = 18;
    private const CHECK_INTERVAL = 10; 

    public function __construct(string $referenceId, int $attemptCount = 1)
    {
        $this->referenceId = $referenceId;
        $this->attemptCount = $attemptCount;
        
        if ($attemptCount > 1) {
            $this->delay(now()->addSeconds(self::CHECK_INTERVAL));
        }
    }

    public function handle(): void
    {
        Log::info('� MTN Payment Verification Job Started', [
            'reference_id' => $this->referenceId,
            'attempt' => $this->attemptCount,
            'max_attempts' => self::MAX_ATTEMPTS,
        ]);

        try {
            // Initialize MTN gateway
            $mtnGateway = new MTNMoneyTestGateway();
            
            // Call the gateway's verify method
            $result = $mtnGateway->verify($this->referenceId, $this->attemptCount);
            
            // Handle the result
            if (!$result['success']) {
                // Verification failed - check if should retry
                if ($result['should_retry']) {
                    Log::info('� MTN Verification Failed, Scheduling Retry', [
                        'reference_id' => $this->referenceId,
                        'attempt' => $this->attemptCount,
                        'status' => $result['status'],
                        'next_attempt_in' => self::CHECK_INTERVAL . ' seconds',
                    ]);
                    
                    $this->scheduleNextAttempt();
                } else {
                    Log::error('🏁 MTN Verification Completed - Failed', [
                        'reference_id' => $this->referenceId,
                        'final_status' => $result['status'],
                        'message' => $result['message'],
                        'attempts' => $result['total_attempts'] ?? $this->attemptCount,
                    ]);
                }
                return;
            }

            // Verification was successful or pending
            if ($result['should_retry']) {
                Log::info('⏳ MTN Payment Still Pending, Scheduling Next Check', [
                    'reference_id' => $this->referenceId,
                    'attempt' => $this->attemptCount,
                    'status' => $result['status'],
                    'next_check_in' => self::CHECK_INTERVAL . ' seconds',
                ]);
                
                $this->scheduleNextAttempt();
            } else {
                Log::info('🏁 MTN Verification Completed - Success', [
                    'reference_id' => $this->referenceId,
                    'final_status' => $result['status'],
                    'message' => $result['message'],
                    'attempts' => $result['total_attempts'] ?? $this->attemptCount,
                    'total_time' => $result['total_time_seconds'] ?? 'N/A',
                ]);
            }

        } catch (\Exception $e) {
            Log::error('💥 MTN Verification Job Exception', [
                'reference_id' => $this->referenceId,
                'attempt' => $this->attemptCount,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Retry on exception if not at max attempts
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
            'payment_id' => $this->referenceId,
            'current_attempt' => $this->attemptCount,
            'next_attempt' => $nextAttempt,
            'delay' => self::CHECK_INTERVAL . ' seconds',
        ]);

        dispatch(new self($this->referenceId, $nextAttempt));
    }



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