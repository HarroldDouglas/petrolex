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
use App\Services\Order\OrderService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(
        private PaymentGatewayFactory $gatewayFactory,
        private OrderService $orderService
    ) {}

    public function initiatePayment(Order $order, PaymentMethod $method, array $paymentDetails = []): OrderPayment
    {
        $payment = $this->createOrderPayment($order, $method);

        $payment->load('order.customer.user');

        $paymentDetailsDto = PaymentDetailsData::from($paymentDetails);

        $gateway = $this->gatewayFactory->create($method->value);
        $response = $gateway->initiatePayment($payment, $paymentDetailsDto);
        $this->updatePaymentFromResponse($payment, $response);

        $this->schedulePaymentCallback($payment, $response);

        return $payment->refresh();
    }

    public function handleCallback(string $orderId, array $callbackData): void
    {
        DB::transaction(function () use ($orderId, $callbackData) {
            $payment = $this->findPaymentByOrderId($orderId);
            $gateway = $this->gatewayFactory->create($payment->payment_method->value);
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
        return OrderPayment::create([
            'order_id' => $order->id,
            'payment_method' => $method->value,
            'amount_paid' => $order->total_amount,
            'amount_due' => $order->total_amount,
            'payment_status' => PaymentStatus::PENDING()->value,
            'payment_reference' => 'PETROLEX_'.uniqid(),
        ]);
    }

    private function updatePaymentFromResponse(OrderPayment $payment, PaymentResponse $response): void
    {
        $payment->update([
            'payment_status' => $response->status,
            'payment_date' => ($response->status === PaymentStatus::PAID()->value) ? now() : null,
            'amount_paid' => ($response->status === PaymentStatus::PAID()->value) ? $payment->amount_due : $payment->amount_paid,
            'amount_due' => ($response->status === PaymentStatus::PAID()->value) ? 0 : $payment->amount_due,
            'payment_notes' => $response->notes ?? null,
        ]);
    }

    private function findPaymentByOrderId(string $orderId): OrderPayment
    {
        $payment = OrderPayment::where('order_id', $orderId)->first();

        if (! $payment) {
            throw (new \Illuminate\Database\Eloquent\ModelNotFoundException)->setModel(OrderPayment::class, [$orderId]);
        }

        return $payment;
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
        $payment->update([
            'payment_status' => $response->status,
            'payment_date' => ($response->status === PaymentStatus::PAID()->value) ? now() : null,
            'amount_paid' => ($response->status === PaymentStatus::PAID()->value) ? $payment->amount_due : $payment->amount_paid,
            'amount_due' => ($response->status === PaymentStatus::PAID()->value) ? 0 : $payment->amount_due,
            'payment_notes' => $response->notes ?? null,
        ]);

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
