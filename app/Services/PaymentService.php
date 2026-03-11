<?php

namespace App\Services;

use App\DTOs\PaymentCallbackData;
use App\DTOs\PaymentDetailsData;
use App\DTOs\PaymentResponse;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Repositories\Contracts\OrderPaymentRepositoryInterface;
use App\Services\Order\OrderService;
use App\Services\Wallet\WalletService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public function __construct(
        private PaymentGatewayFactory $gatewayFactory,
        private OrderService $orderService,
        private OrderPaymentRepositoryInterface $orderPaymentRepository,
        private WalletService $walletService
    ) {}

    /**
     * Initiate payment with wallet support.
     *
     * This method first checks the customer's wallet balance.
     * If wallet has sufficient funds, the order is paid immediately.
     * If not, it uses wallet balance and requires external payment for the remaining amount.
     *
     * @return array{
     *     order_payment: OrderPayment|null,
     *     wallet_used: bool,
     *     wallet_amount: float,
     *     external_payment_required: bool,
     *     external_payment_amount: float,
     *     payment_breakdown: array
     * }
     */
    public function initiatePaymentWithWallet(
        Order $order,
        PaymentMethod $method,
        array $paymentDetails = [],
        bool $useWallet = true
    ): array {
        return DB::transaction(function () use ($order, $method, $paymentDetails, $useWallet) {
            $customer = $order->customer;
            $totalAmount = (float) $order->total_amount;

            // Calculate payment breakdown
            $breakdown = $this->walletService->calculatePaymentBreakdown($customer, $totalAmount);

            Log::info('Payment breakdown calculated', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'total_amount' => $totalAmount,
                'wallet_balance' => $breakdown['wallet_balance_before'],
                'wallet_amount' => $breakdown['wallet_amount'],
                'payment_amount' => $breakdown['payment_amount'],
                'use_wallet' => $useWallet,
            ]);

            $walletAmountUsed = 0;
            $walletTransaction = null;

            // Use wallet if enabled and has balance
            if ($useWallet && $breakdown['wallet_amount'] > 0) {
                $walletResult = $this->walletService->processOrderPayment($customer, $order, true);
                $walletTransaction = $walletResult['wallet_transaction'];
                $walletAmountUsed = $walletResult['wallet_amount_used'];

                Log::info('Wallet payment processed', [
                    'order_id' => $order->id,
                    'wallet_amount_used' => $walletAmountUsed,
                    'transaction_reference' => $walletTransaction?->reference,
                    'external_payment_required' => $walletResult['external_payment_required'],
                ]);
            }

            // If wallet covers full amount, mark order as paid
            if ($useWallet && $breakdown['wallet_sufficient']) {
                $this->markOrderAsPaidByWallet($order, $walletTransaction);

                return [
                    'order_payment' => null,
                    'wallet_used' => true,
                    'wallet_amount' => $walletAmountUsed,
                    'external_payment_required' => false,
                    'external_payment_amount' => 0,
                    'payment_breakdown' => array_merge($breakdown, [
                        'payment_status' => 'completed',
                        'wallet_transaction_reference' => $walletTransaction?->reference,
                    ]),
                ];
            }

            // External payment required (for remaining amount)
            $externalAmount = $useWallet ? $breakdown['payment_amount'] : $totalAmount;

            // Create order payment for the remaining amount
            $payment = $this->createOrderPaymentWithWalletInfo(
                $order,
                $method,
                $externalAmount,
                $walletAmountUsed,
                $walletTransaction?->reference
            );

            $payment->load('order.customer.user');

            $paymentDetailsDto = PaymentDetailsData::from($paymentDetails);

            $gateway = $this->gatewayFactory->create($method->value);
            $response = $gateway->initiatePayment($payment, $paymentDetailsDto);
            $this->updatePaymentFromResponse($payment, $response);

            Log::info('External payment initiated', [
                'payment_id' => $payment->id,
                'gateway_response' => $response->gatewayResponse,
                'order_number' => $payment->order->order_number,
                'transaction_reference' => $response->transactionReference,
                'status' => $response->status,
                'external_amount' => $externalAmount,
            ]);

            $this->schedulePaymentCallback($payment, $response);

            return [
                'order_payment' => $payment->refresh(),
                'wallet_used' => $walletAmountUsed > 0,
                'wallet_amount' => $walletAmountUsed,
                'external_payment_required' => true,
                'external_payment_amount' => $externalAmount,
                'payment_breakdown' => array_merge($breakdown, [
                    'payment_status' => 'pending_external_payment',
                    'wallet_transaction_reference' => $walletTransaction?->reference,
                    'external_payment_reference' => $payment->payment_reference,
                ]),
            ];
        });
    }

    /**
     * Mark order as paid when fully covered by wallet
     */
    private function markOrderAsPaidByWallet(Order $order, $walletTransaction): void
    {
        $this->orderService->update($order, [
            'status' => OrderStatus::PAID()->value,
            'paid_at' => now(),
        ]);

        Log::info('Order marked as paid by wallet', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'wallet_transaction_reference' => $walletTransaction?->reference,
        ]);
    }

    /**
     * Create order payment with wallet information
     */
    private function createOrderPaymentWithWalletInfo(
        Order $order,
        PaymentMethod $method,
        float $amountDue,
        float $walletAmountUsed,
        ?string $walletTransactionReference
    ): OrderPayment {
        /** @var OrderPayment */
        return $this->orderPaymentRepository->create([
            'order_id' => $order->id,
            'payment_method' => $method->value,
            'amount_paid' => $walletAmountUsed,
            'amount_due' => $amountDue,
            'payment_status' => PaymentStatus::PENDING()->value,
            'payment_reference' => 'PTX'.uniqid(), // Max 20 chars for Orange Money
            'transaction_reference' => null,
            'payment_url' => null,
            'gateway_response' => null,
            'payment_notes' => $walletAmountUsed > 0
                ? "Wallet used: {$walletAmountUsed} FCFA (ref: {$walletTransactionReference})"
                : null,
        ]);
    }

    public function initiatePayment(Order $order, PaymentMethod $method, array $paymentDetails = []): OrderPayment
    {
        $payment = $this->createOrderPayment($order, $method);

        $payment->load('order.customer.user');

        $paymentDetailsDto = PaymentDetailsData::from($paymentDetails);

        $gateway = $this->gatewayFactory->create($method->value);
        $response = $gateway->initiatePayment($payment, $paymentDetailsDto);
        $this->updatePaymentFromResponse($payment, $response);

        Log::info('gateway response', [
            'payment_id' => $payment->id,
            'gateway_response' => $response->gatewayResponse,
            'order_number' => $payment->order->order_number,
            'transaction_reference' => $response->transactionReference,
            'status' => $response->status,
        ]);

        $this->schedulePaymentCallback($payment, $response);

        return $payment->refresh();
    }

    public function handleCallback(string $orderId, array $callbackData): void
    {
        DB::transaction(function () use ($orderId, $callbackData) {
            $payment = $this->orderPaymentRepository->findByOrderId($orderId);

            $gateway = $this->gatewayFactory->create($payment?->payment_method?->value);
            Log::info('🔔 Received Payment Callback', [
                'payment_id' => $payment->id,
                'order_id' => $payment->order->id,
                'order_number' => $payment->order->order_number,
                'callback_data' => $callbackData,
            ]);

            $callbackDto = new PaymentCallbackData(
                transactionReference: $callbackData['transaction_ref'],
                status: $this->mapTransactionStatusToPaymentStatus($callbackData['transaction_status']),
                amount: $callbackData['transaction_amount'],
                rawData: $callbackData
            );

            Log::info('🔔 Handling Payment Callback', [
                'payment_id' => $payment->id,
                'order_id' => $payment->order->id,
                'order_number' => $payment->order->order_number,
                'transaction_reference' => $callbackDto->transactionReference,
                'status' => $callbackDto->status,
                'amount' => $callbackDto->amount,
            ]);

            $response = new PaymentResponse(
                success: $callbackDto->status === PaymentStatus::PAID()->value,
                status: $callbackDto->status,
                transactionReference: $callbackDto->transactionReference,
                paymentUrl: null,
                amount: $callbackDto->amount,
                errorMessage: $callbackData['failure_reason'] ?? null,
                gatewayResponse: $callbackDto->rawData
            );

            $this->processPaymentResponse($payment, $response);
        });
    }

    private function createOrderPayment(Order $order, PaymentMethod $method): OrderPayment
    {
        /** @var OrderPayment */
        return $this->orderPaymentRepository->create([
            'order_id' => $order->id,
            'payment_method' => $method->value,
            'amount_paid' => 0,
            'amount_due' => $order->total_amount,
            'payment_status' => PaymentStatus::PENDING()->value,
            'payment_reference' => 'PTX'.uniqid(), // Max 20 chars for Orange Money
            'transaction_reference' => null,
            'payment_url' => null,
            'gateway_response' => null,
        ]);
    }

    private function updatePaymentFromResponse(OrderPayment $payment, PaymentResponse $response): void
    {
        $updateData = [
            'payment_status' => $response->status,
            'payment_date' => ($response->status === PaymentStatus::PAID()->value) ? now() : null,
            'amount_paid' => ($response->status === PaymentStatus::PAID()->value) ? $payment->amount_due : $payment->amount_paid,
            'amount_due' => ($response->status === PaymentStatus::PAID()->value) ? 0 : $payment->amount_due,
            'payment_notes' => $response->errorMessage ?? null,
            'gateway_response' => $response->gatewayResponse,
            'transaction_reference' => $response->transactionReference,
            'payment_url' => $response->paymentUrl,
        ];

        $this->orderPaymentRepository->update($payment, $updateData);
    }

    private function mapTransactionStatusToPaymentStatus(string $transactionStatus): string
    {
        $statusMappings = config('payment.status_mappings', []);
        $normalizedStatus = strtoupper($transactionStatus);

        return match (true) {
            in_array($normalizedStatus, $statusMappings['success_statuses'] ?? []) => PaymentStatus::PAID()->value,
            in_array($normalizedStatus, $statusMappings['failed_statuses'] ?? []) => PaymentStatus::FAILED()->value,
            default => PaymentStatus::PENDING()->value,
        };
    }

    private function processPaymentResponse(OrderPayment $payment, PaymentResponse $response): void
    {
        Log::info('Processing payment response', [
            'payment_id' => $payment->id,
            'response' => $response,
        ]);

        $status = $response->status;
        $amountDue = $payment->amount_due;
        $now = now();

        $updateData = [
            'payment_status' => $status,
            'payment_notes' => $response->errorMessage ?? null,
            'gateway_response' => $response,
            'transaction_reference' => $response->transactionReference,
            'payment_date' => in_array($status, [PaymentStatus::PAID()->value, PaymentStatus::FAILED()->value]) ? $now : null,
            'amount_paid' => $status === PaymentStatus::PAID()->value ? $amountDue : 0,
            'amount_due' => $status === PaymentStatus::PAID()->value ? 0 : $amountDue,
        ];

        $updateData = array_filter($updateData, fn ($value) => ! is_null($value));

        $this->orderPaymentRepository->update($payment, $updateData);

        Log::info('Ready to update order', [
            'order_id' => $payment->order->id,
            'order_number' => $payment->order->order_number,
            'response_status' => $status,
            'response_success' => $response->success,
        ]);

        $orderUpdateData = match ($status) {
            PaymentStatus::PAID()->value => [
                'status' => OrderStatus::PAID()->value,
                'paid_at' => $now,
            ],
            PaymentStatus::FAILED()->value => [
                'status' => OrderStatus::FAILED()->value,
            ],
            default => null
        };

        if ($orderUpdateData && ($response->success || $status === PaymentStatus::FAILED()->value)) {
            $currentOrder = $payment->order->fresh();

            if ($status === PaymentStatus::PAID()->value && $currentOrder->status->value === OrderStatus::PAID()->value) {
                Log::info('Order is already paid, skipping update', [
                    'order_id' => $currentOrder->id,
                    'order_number' => $currentOrder->order_number,
                    'current_status' => $currentOrder->status->value,
                    'paid_at' => $currentOrder->paid_at,
                ]);

                return;
            }

            // If payment failed and wallet was used, restore the wallet amount
            if ($status === PaymentStatus::FAILED()->value) {
                $this->restoreWalletOnPaymentFailure($payment);
            }

            Log::info('Updating order status via OrderService', [
                'order_id' => $payment->order->id,
                'order_number' => $payment->order->order_number,
                'new_status' => $orderUpdateData['status'],
            ]);

            $this->orderService->update($payment->order, $orderUpdateData);
        }
    }

    /**
     * Restore wallet amount when external payment fails.
     * This is called when a partial wallet payment was made but the external payment failed.
     */
    private function restoreWalletOnPaymentFailure(OrderPayment $payment): void
    {
        // Check if wallet was used (amount_paid > 0 means wallet was partially used)
        $walletAmountUsed = (float) $payment->amount_paid;

        if ($walletAmountUsed <= 0) {
            return;
        }

        $customer = $payment->order->customer;
        if (! $customer) {
            Log::error('Cannot restore wallet: customer not found', [
                'payment_id' => $payment->id,
                'order_id' => $payment->order_id,
            ]);

            return;
        }

        // Credit back the wallet amount
        $this->walletService->credit(
            $customer,
            $walletAmountUsed,
            $payment->order,
            __('wallet.payment_failed_refund', ['order_number' => $payment->order->order_number]),
            [
                'payment_id' => $payment->id,
                'payment_reference' => $payment->payment_reference,
                'reason' => 'external_payment_failed',
            ]
        );

        Log::info('Wallet restored after payment failure', [
            'customer_id' => $customer->id,
            'order_id' => $payment->order_id,
            'amount_restored' => $walletAmountUsed,
        ]);
    }

    private function schedulePaymentCallback(OrderPayment $payment, PaymentResponse $response): void
    {
        $referenceId = $response->transactionReference ?? $payment->payment_reference;

        dispatch(new \App\Jobs\VerifyPaymentStatusJob(
            $referenceId,
            $payment->payment_method,
            1
        ))->delay(now()->addSeconds(30));
    }
}
