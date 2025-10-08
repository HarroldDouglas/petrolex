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

        // Load necessary relationships for the payment gateway
        $payment->load('order.customer.user');

        // Create payment details DTO
        $paymentDetailsDto = PaymentDetailsData::from($paymentDetails);

        $gateway = $this->gatewayFactory->create($method->value);
        $response = $gateway->initiatePayment($payment, $paymentDetailsDto);
        $this->updatePaymentFromResponse($payment, $response);

        // TODO: Remove this simulation when real payment callbacks are implemented
        $this->schedulePaymentCallback($payment, $response);

        return $payment->refresh();
    }

    public function handleCallback(string $orderId, array $callbackData): void
    {
        DB::transaction(function () use ($orderId, $callbackData) {
            $payment = $this->findPaymentByOrderId($orderId);
            $gateway = $this->gatewayFactory->create($payment->payment_method->value);
            $callbackDto = new PaymentCallbackData(
                transactionReference: $callbackData['transaction_ref'],
                status: $this->mapTransactionStatusToPaymentStatus($callbackData['transaction_status']),
                amount: $callbackData['transaction_amount'],
                rawData: $callbackData
            );

            // Create PaymentResponse from callback data
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
            'amount_paid' => $order->total_amount, // Assuming order has total_amount
            'amount_due' => $order->total_amount, // Initially, amount due is total amount
            'payment_status' => PaymentStatus::PENDING()->value,
            'payment_reference' => 'PETROLEX_'.uniqid(), // Generate a unique reference
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

    /**
     * Update payment status and related order status
     * This method ensures both payment and order are updated properly with events
     */
    public function updatePaymentStatus(OrderPayment $payment, PaymentStatus $newStatus, ?string $notes = null): void
    {
        Log::info('Updating payment status', [
            'payment_id' => $payment->id,
            'old_status' => $payment->payment_status->value,
            'new_status' => $newStatus->value,
        ]);

        // Update payment record
        $updateData = [
            'payment_status' => $newStatus->value,
            'payment_date' => ($newStatus === PaymentStatus::PAID()) ? now() : null,
            'amount_paid' => ($newStatus === PaymentStatus::PAID()) ? $payment->amount_due : $payment->amount_paid,
            'amount_due' => ($newStatus === PaymentStatus::PAID()) ? 0 : $payment->amount_due,
        ];

        if ($notes) {
            $updateData['payment_notes'] = $notes;
        }

        $payment->update($updateData);

        // Update order status using OrderService to ensure events are fired
        if ($newStatus === PaymentStatus::PAID()) {
            $this->orderService->update($payment->order, [
                'status' => OrderStatus::PAID()->value,
                'paid_at' => now(),
            ]);
        } elseif ($newStatus === PaymentStatus::FAILED()) {
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
