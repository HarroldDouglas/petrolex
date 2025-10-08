<?php

namespace App\Jobs;

use App\Enums\PaymentMethod;
use App\Services\PaymentGatewayFactory;
use App\Services\PaymentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class VerifyPaymentStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 300;

    private string $referenceId;
    private int $attemptCount;

    private const MAX_ATTEMPTS = 18;
    private const CHECK_INTERVAL = 10;

    private PaymentGatewayFactory $gatewayFactory;

    private PaymentMethod $paymentMethod;

    private PaymentService $paymentService;

    public function __construct(string $referenceId, PaymentMethod $paymentMethod, PaymentService $paymentService, int $attemptCount = 1)
    {
        $this->referenceId = $referenceId;
        $this->attemptCount = $attemptCount;
        $this->paymentMethod = $paymentMethod;
        $this->gatewayFactory = new PaymentGatewayFactory;
        $this->paymentService = $paymentService;
    }

    public function handle(): void
    {
        Log::info('🚀 '.$this->paymentMethod.' NEW Payment Status Verification Job Started', [
            'reference_id' => $this->referenceId,
            'attempt' => $this->attemptCount,
        ]);

        try {
            // Ask the concrete gateway to verify status for the reference id
            $gateway = $this->gatewayFactory->create($this->paymentMethod->value);

            $response = $gateway->verifyPayment($this->referenceId);
            // Normalize to array structure expected below
            $result = [
                'success' => $response->success,
                'status' => $response->status,
                'amount' => $response->amount ?? null,
                'should_retry' => $response->status === 'PENDING',
            ];

            Log::info('📋 NEW IN JOB PAYMENT Verification Result', [
                'reference_id' => $this->referenceId,
                'attempt' => $this->attemptCount,
                'result' => $result,
            ]);

            if (! $response->success) {
                Log::info('💡  Payment Verification Not Successful', [
                    'reference_id' => $this->referenceId,
                    'attempt' => $this->attemptCount,
                    'response' => $response,
                ]);
                if (! empty($result['should_retry']) && $this->attemptCount < self::MAX_ATTEMPTS) {
                    Log::info('⏳'.$this->paymentMethod.' Verification Failed, Scheduling Retry', [
                        'reference_id' => $this->referenceId,
                        'attempt' => $this->attemptCount,
                        'status' => $result['status'] ?? null,
                        'next_attempt_in' => self::CHECK_INTERVAL.' seconds',
                    ]);
                    $this->scheduleNextAttempt();
                } else {
                    Log::error('🏁 '.$this->paymentMethod.' NEW Verification Completed - Failed', [
                        'reference_id' => $this->referenceId,
                        'final_status' => $result['status'] ?? null,
                        'message' => $result['message'] ?? null,
                        'attempts' => $result['total_attempts'] ?? $this->attemptCount,
                    ]);
                }

                return;
            }

            if ($result['should_retry'] ?? false) {
                Log::info('⏳'.$this->paymentMethod.' NEW Payment Still Pending, Scheduling Next Check', [
                    'reference_id' => $this->referenceId,
                    'attempt' => $this->attemptCount,
                    'status' => $result['status'] ?? null,
                    'next_check_in' => self::CHECK_INTERVAL.' seconds',
                ]);
                $this->scheduleNextAttempt();
            } else {
                Log::info('✅ IN JOB '.$this->paymentMethod.' NEW Payment Verification Successful', [
                    'reference_id' => $this->referenceId,
                    'attempt' => $this->attemptCount,
                    'status' => $result['status'] ?? null,
                ]);

                // Find the order payment using the external ID (PETROLEX reference) from gateway response
                $gatewayResponse = $response->gatewayResponse ?? [];
                $externalId = $gatewayResponse['externalId'] ?? null;
                
                if (!$externalId) {
                    Log::error('❌ Cannot find externalId in gateway response for callback', [
                        'reference_id' => $this->referenceId,
                        'gateway_response' => $gatewayResponse,
                    ]);
                    return;
                }

                // Find the OrderPayment by payment_reference (PETROLEX reference)
                $orderPayment = \App\Models\OrderPayment::where('payment_reference', $externalId)->first();
                
                if (!$orderPayment) {
                    Log::error('❌ Cannot find OrderPayment for externalId', [
                        'external_id' => $externalId,
                        'mtn_reference' => $this->referenceId,
                    ]);
                    return;
                }

                Log::info('🔗 Found OrderPayment for callback', [
                    'external_id' => $externalId,
                    'mtn_reference' => $this->referenceId,
                    'order_payment_id' => $orderPayment->id,
                    'order_id' => $orderPayment->order_id,
                ]);

                // Call handleCallback with the order ID (not the MTN reference)
                $this->paymentService->handleCallback(
                    (string) $orderPayment->order_id,
                    $result + [
                        'transaction_ref' => $result['reference_id'] ?? $this->referenceId,
                        'transaction_status' => $result['status'] ?? null,
                        'transaction_amount' => $result['amount'] ?? null,
                    ]
                );

                Log::info('🏁 '.$this->paymentMethod.' NEW Verification Completed - Success', [
                    'reference_id' => $this->referenceId,
                    'final_status' => $result['status'] ?? null,
                    'message' => $result['message'] ?? null,
                    'attempts' => $result['total_attempts'] ?? $this->attemptCount,
                    'total_time' => $result['total_time_seconds'] ?? 'N/A',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('💥 '.$this->paymentMethod.' NEW Verification Job Exception', [
                'reference_id' => $this->referenceId,
                'attempt' => $this->attemptCount,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($this->attemptCount < self::MAX_ATTEMPTS) {
                Log::info('🔄 NEW Scheduling Retry After Exception', [
                    'reference_id' => $this->referenceId,
                    'current_attempt' => $this->attemptCount,
                    'next_attempt' => $this->attemptCount + 1,
                ]);
                $this->scheduleNextAttempt();
            } else {
                Log::error('🚫 NEW Max Attempts Reached After Exception', [
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

        Log::info('📅 NEW Scheduling Next Payment Status Check', [
            'reference_id' => $this->referenceId,
            'current_attempt' => $this->attemptCount,
            'next_attempt' => $nextAttempt,
            'delay' => self::CHECK_INTERVAL.' seconds',
        ]);

        dispatch((new self($this->referenceId, $this->paymentMethod, $this->paymentService, $nextAttempt))->delay(now()->addSeconds(self::CHECK_INTERVAL)));
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('💥 NEW Payment Status Job Failed Completely', [
            'reference_id' => $this->referenceId,
            'attempt' => $this->attemptCount,
            'exception' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
