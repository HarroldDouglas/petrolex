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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(
        private PaymentGatewayFactory $gatewayFactory,
        private OrderService $orderService,
        private OrderPaymentRepositoryInterface $orderPaymentRepository
    ) {}

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
                errorMessage: null,
                gatewayResponse: $callbackDto->rawData
            );

            $this->processPaymentResponse($payment, $response);
        });
    }

    private function createOrderPayment(Order $order, PaymentMethod $method): OrderPayment
    {
        return $this->orderPaymentRepository->create([
            'order_id' => $order->id,
            'payment_method' => $method->value,
            'amount_paid' => 0,
            'amount_due' => $order->total_amount,
            'payment_status' => PaymentStatus::PENDING()->value,
            'payment_reference' => 'PETROLEX_'.uniqid(),
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

        return match (true) {
            in_array($transactionStatus, $statusMappings['success_statuses'] ?? []) => PaymentStatus::PAID()->value,
            in_array($transactionStatus, $statusMappings['failed_statuses'] ?? []) => PaymentStatus::FAILED()->value,
            default => PaymentStatus::PENDING()->value,
        };
    }

    private function processPaymentResponse(OrderPayment $payment, PaymentResponse $response): void
    {
        Log::info('Processing payment response', [
            'payment_id' => $payment->id,
            'response' => $response,
        ]);

        // Update payment record
        $updateData = [
            'payment_status' => $response->status,
            'payment_notes' => $response->notes ?? null,
            'gateway_response' => $response->gatewayResponse,
        ];

        // Handle successful payments
        if ($response->status === PaymentStatus::PAID()->value) {
            $updateData['payment_date'] = now();
            $updateData['amount_paid'] = $payment->amount_due;
            $updateData['amount_due'] = 0;
        }
        
        // Handle failed payments
        elseif ($response->status === PaymentStatus::FAILED()->value) {
            $updateData['payment_date'] = now();
            // Keep original amounts for failed payments
            $updateData['amount_paid'] = 0;
            $updateData['amount_due'] = $payment->amount_due;
        }

        // Add transaction reference if available
        if ($response->transactionReference) {
            $updateData['transaction_reference'] = $response->transactionReference;
        }

        $this->orderPaymentRepository->update($payment, $updateData);

        Log::info('Ready to update order', [
            'order_id' => $payment->order->id,
            'order_number' => $payment->order->order_number,
            'response_status' => $response->status,
            'response_success' => $response->success,
        ]);

        // Update order status using OrderService to ensure events are fired
        if ($response->success && $response->status === PaymentStatus::PAID()->value) {
            Log::info('Payment successful, updating order status via OrderService', [
                'order_id' => $payment->order->id,
                'order_number' => $payment->order->order_number,
            ]);
            $this->orderService->update($payment->order, [
                'status' => OrderStatus::PAID()->value,
                'paid_at' => now(),
            ]);
        } elseif ($response->status === PaymentStatus::FAILED()->value) {
            Log::info('Payment failed, updating order status via OrderService', [
                'order_id' => $payment->order->id,
                'order_number' => $payment->order->order_number,
            ]);
            $this->orderService->update($payment->order, [
                'status' => OrderStatus::FAILED()->value,
            ]);
        }
    }

    private function schedulePaymentCallback(OrderPayment $payment, PaymentResponse $response): void
    {
        $referenceId = $response->transactionReference ?? $payment->payment_reference;
        
        dispatch(new \App\Jobs\VerifyPaymentStatusJob(
            $referenceId,
            $payment->payment_method,
            $this
        ))->delay(now()->addSeconds(30));
    }
}
