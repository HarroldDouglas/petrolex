<?php

namespace App\Jobs;

use App\Enums\PaymentMethod;
use App\Repositories\Contracts\OrderPaymentRepositoryInterface;
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

    // Runtime dependencies (resolved in handle(), not serialized)
    private PaymentService $paymentService;
    private OrderPaymentRepositoryInterface $orderPaymentRepository;

    private const MAX_ATTEMPTS = 30; // 30 attempts × 10 seconds = 5 minutes
    private const CHECK_INTERVAL = 10;
    private const MAX_RETRY_ATTEMPTS = 5;
    private const RETRY_BASE_DELAY = 30;
    private const TIMEOUT_CAP = 300; // Maximum delay in seconds (5 minutes)

    private PaymentMethod $paymentMethod;

    public function __construct(
        string $referenceId,
        PaymentMethod $paymentMethod,
        int $attemptCount = 1
    ) {
        $this->referenceId = $referenceId;
        $this->attemptCount = $attemptCount;
        $this->paymentMethod = $paymentMethod;
    }

    public function handle(): void
    {
        // Resolve dependencies here (not in constructor) because job is unserialized from queue
        $gatewayFactory = new PaymentGatewayFactory();
        $this->paymentService = app(PaymentService::class);
        $this->orderPaymentRepository = app(OrderPaymentRepositoryInterface::class);

        Log::info('🚀 '.$this->paymentMethod.' NEW Payment Status Verification Job Started', [
            'reference_id' => $this->referenceId,
            'attempt' => $this->attemptCount,
        ]);

        try {
            $gateway = $gatewayFactory->create($this->paymentMethod->value);

            $response = $gateway->verifyPayment($this->referenceId);

            $isNetworkTimeout = $this->isNetworkTimeoutError($response->errorMessage ?? '');
            $shouldRetry = $response->status === 'PENDING' ||
                          ($response->status === 'FAILED' && $isNetworkTimeout);

            $result = [
                'success' => $response->success,
                'status' => $response->status,
                'amount' => $response->amount ?? null,
                'should_retry' => $shouldRetry,
                'is_network_timeout' => $isNetworkTimeout,
            ];

            Log::info('📋 NEW IN JOB PAYMENT Verification Result', [
                'reference_id' => $this->referenceId,
                'attempt' => $this->attemptCount,
                'result' => $result,
                'retry_logic' => [
                    'should_retry' => $shouldRetry,
                    'is_pending' => $response->status === 'PENDING',
                    'is_failed_with_network_timeout' => $response->status === 'FAILED' && $isNetworkTimeout,
                    'error_message' => $response->errorMessage ?? null,
                ],
            ]);

            if (! $response->success) {
                Log::info('💡  Payment Verification Not Successful', [
                    'reference_id' => $this->referenceId,
                    'attempt' => $this->attemptCount,
                    'response' => $response,
                ]);

                $isNetworkTimeout = $result['is_network_timeout'] ?? false;

                if ($isNetworkTimeout && $this->attemptCount < self::MAX_RETRY_ATTEMPTS) {
                    $retryDelay = $this->calculateRetryDelay();
                    Log::info('🔄 Network timeout detected, scheduling retry with exponential backoff', [
                        'reference_id' => $this->referenceId,
                        'attempt' => $this->attemptCount,
                        'retry_delay_seconds' => $retryDelay,
                        'error_message' => $response->errorMessage ?? '',
                    ]);
                    $this->scheduleRetryAttempt($retryDelay);
                } elseif ($result['should_retry'] && ! $isNetworkTimeout && $this->attemptCount < self::MAX_ATTEMPTS) {
                    Log::info('⏳'.$this->paymentMethod.' Payment Still Pending, Scheduling Retry', [
                        'reference_id' => $this->referenceId,
                        'attempt' => $this->attemptCount,
                        'status' => $result['status'] ?? null,
                        'next_attempt_in' => self::CHECK_INTERVAL.' seconds',
                    ]);
                    $this->scheduleNextAttempt();
                } else {
                    $this->handleFinalFailure($response, $isNetworkTimeout);
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

                $gatewayResponse = $response->gatewayResponse ?? [];
                $externalId = $gatewayResponse['externalId'] ?? null;

                Log::info('🔍 Searching for OrderPayment', [
                    'reference_id' => $this->referenceId,
                    'gateway_response_keys' => array_keys($gatewayResponse),
                    'external_id' => $externalId,
                    'full_gateway_response' => $gatewayResponse,
                ]);

                if (! $externalId) {
                    Log::error('❌ Cannot find externalId in gateway response for callback', [
                        'reference_id' => $this->referenceId,
                        'gateway_response' => $gatewayResponse,
                        'available_keys' => array_keys($gatewayResponse),
                    ]);

                    Log::info('🔄 Attempting alternative payment lookup methods', [
                        'mtn_reference' => $this->referenceId,
                    ]);

                    Log::warning('⚠️ Skipping payment callback due to missing externalId', [
                        'reference_id' => $this->referenceId,
                        'attempt' => $this->attemptCount,
                    ]);

                    return;
                }

                $orderPayment = $this->findOrderPaymentByReference($externalId);

                if (! $orderPayment) {
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

                if (! $orderPayment->order_id || ! is_numeric($orderPayment->order_id)) {
                    Log::error('❌ Invalid order_id for callback', [
                        'order_payment_id' => $orderPayment->id,
                        'order_id' => $orderPayment->order_id,
                        'external_id' => $externalId,
                    ]);

                    return;
                }

                $orderIdForCallback = (string) $orderPayment->order_id;

                try {
                    $this->paymentService->handleCallback(
                        $orderIdForCallback,
                        $result + [
                            'transaction_ref' => $result['reference_id'] ?? $this->referenceId,
                            'transaction_status' => $result['status'] ?? null,
                            'transaction_amount' => $result['amount'] ?? null,
                        ]
                    );

                    Log::info('✅ Payment callback processed successfully', [
                        'order_id' => $orderPayment->order_id,
                        'external_id' => $externalId,
                        'mtn_reference' => $this->referenceId,
                    ]);
                } catch (\Exception $callbackException) {
                    Log::error('💥 Payment callback failed', [
                        'order_id' => $orderPayment->order_id,
                        'external_id' => $externalId,
                        'mtn_reference' => $this->referenceId,
                        'callback_error' => $callbackException->getMessage(),
                        'callback_trace' => $callbackException->getTraceAsString(),
                    ]);
                }

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

            $isNetworkTimeout = $this->isNetworkTimeoutError($e->getMessage());

            if ($isNetworkTimeout && $this->attemptCount < self::MAX_RETRY_ATTEMPTS) {
                $retryDelay = $this->calculateRetryDelay();
                Log::info('🔄 Exception network timeout detected, scheduling retry', [
                    'reference_id' => $this->referenceId,
                    'current_attempt' => $this->attemptCount,
                    'retry_delay_seconds' => $retryDelay,
                ]);
                $this->scheduleRetryAttempt($retryDelay);
            } elseif ($this->attemptCount < self::MAX_ATTEMPTS) {
                Log::info('🔄 NEW Scheduling Retry After Exception', [
                    'reference_id' => $this->referenceId,
                    'current_attempt' => $this->attemptCount,
                    'next_attempt' => $this->attemptCount + 1,
                ]);
                $this->scheduleNextAttempt();
            } else {
                Log::error('🚫 NEW Max Attempts Reached After Exception', [
                    'reference_id' => $this->referenceId,
                    'max_attempts' => $this->attemptCount >= self::MAX_RETRY_ATTEMPTS ? self::MAX_RETRY_ATTEMPTS : self::MAX_ATTEMPTS,
                    'final_error' => $e->getMessage(),
                ]);

                if ($isNetworkTimeout) {
                    $mockResponse = (object) [
                        'success' => false,
                        'errorMessage' => $e->getMessage(),
                        'gatewayResponse' => null,
                    ];
                    $this->handleFinalFailure($mockResponse, true);
                }
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

        dispatch((new self($this->referenceId, $this->paymentMethod, $nextAttempt))->delay(now()->addSeconds(self::CHECK_INTERVAL)));
    }

    /**
     * Find OrderPayment by payment reference using repository
     */
    private function findOrderPaymentByReference(string $externalId): ?\App\Models\OrderPayment
    {
        try {
            return $this->orderPaymentRepository->findByPaymentReference($externalId);
        } catch (\Exception $e) {
            Log::error('💥 Failed to find OrderPayment by reference', [
                'external_id' => $externalId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Check if the error is a network timeout that should be retried
     */
    private function isNetworkTimeoutError(string $errorMessage): bool
    {
        $timeoutErrors = [
            'cURL error 28:', // Connection timeout
            'cURL error 7:',  // Couldn't connect to host
            'cURL error 6:',  // Couldn't resolve host
            'Connection timeout',
            'Connection timed out',
            'Network timeout',
            'Request timeout',
        ];

        foreach ($timeoutErrors as $timeoutError) {
            if (stripos($errorMessage, $timeoutError) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Calculate retry delay using exponential backoff
     */
    private function calculateRetryDelay(): int
    {
        // Exponential backoff: 30s, 60s, 120s, 240s, 480s (max)
        $delay = self::RETRY_BASE_DELAY * (2 ** ($this->attemptCount - 1));

        // Cap at 5 minutes (300 seconds)
        return min($delay, self::TIMEOUT_CAP);
    }

    /**
     * Schedule retry attempt with exponential backoff delay
     */
    private function scheduleRetryAttempt(int $delaySeconds): void
    {
        $nextAttempt = $this->attemptCount + 1;

        Log::info('📅 Scheduling Network Timeout Retry', [
            'reference_id' => $this->referenceId,
            'current_attempt' => $this->attemptCount,
            'next_attempt' => $nextAttempt,
            'delay_seconds' => $delaySeconds,
        ]);

        dispatch((new self($this->referenceId, $this->paymentMethod, $nextAttempt))
            ->delay(now()->addSeconds($delaySeconds)));
    }

    /**
     * Handle final failure by updating payment status
     */
    private function handleFinalFailure($response, bool $wasNetworkTimeout): void
    {
        $failureReason = $wasNetworkTimeout ? 'Network timeout - max retries exceeded' : 'Payment verification failed';

        Log::error('🏁 '.$this->paymentMethod.' Verification Completed - Final Failure', [
            'reference_id' => $this->referenceId,
            'failure_reason' => $failureReason,
            'attempts' => $this->attemptCount,
            'was_network_timeout' => $wasNetworkTimeout,
        ]);

        try {
            $gatewayResponse = $response->gatewayResponse ?? [];
            $externalId = $gatewayResponse['externalId'] ?? null;

            if (! $externalId) {
                Log::warning('⚠️ Cannot update payment status - no externalId found', [
                    'reference_id' => $this->referenceId,
                ]);

                return;
            }

            $orderPayment = $this->findOrderPaymentByReference($externalId);

            if (! $orderPayment) {
                Log::warning('⚠️ Cannot update payment status - OrderPayment not found', [
                    'external_id' => $externalId,
                    'reference_id' => $this->referenceId,
                ]);

                return;
            }

            $this->paymentService->handleCallback(
                (string) $orderPayment->order_id,
                [
                    'success' => false,
                    'status' => 'FAILED',
                    'amount' => null,
                    'should_retry' => false,
                    'transaction_ref' => $this->referenceId,
                    'transaction_status' => 'FAILED',
                    'transaction_amount' => null,
                ]
            );

            Log::info('✅ Payment status updated to failed', [
                'order_id' => $orderPayment->order_id,
                'external_id' => $externalId,
                'failure_reason' => $failureReason,
            ]);

        } catch (\Exception $e) {
            Log::error('💥 Failed to update payment status to failed', [
                'reference_id' => $this->referenceId,
                'error' => $e->getMessage(),
            ]);
        }
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
