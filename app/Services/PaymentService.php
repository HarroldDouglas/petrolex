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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(
        private PaymentGatewayFactory $gatewayFactory
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
                success: $callbackDto->status === 'SUCCESS',
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
        $endingStates = config('payment.ending_states', []);

        return match (true) {
            in_array($transactionStatus, $endingStates['success']) => PaymentStatus::PAID()->value,
            in_array($transactionStatus, $endingStates['failed']) => PaymentStatus::FAILED()->value,
            in_array($transactionStatus, $endingStates['cancelled']) => PaymentStatus::FAILED()->value,
            default => PaymentStatus::PENDING()->value,
        };
    }

    private function processPaymentResponse(OrderPayment $payment, PaymentResponse $response): void
    {
        Log::info('Processing payment response', [
            'payment_id' => $payment->id,
            'response' => $response,
        ]);

        $payment->update([
            'payment_status' => $response->status,
            'payment_date' => ($response->status === PaymentStatus::PAID()->value) ? now() : null,
            'amount_paid' => ($response->status === PaymentStatus::PAID()->value) ? $payment->amount_due : $payment->amount_paid,
            'amount_due' => ($response->status === PaymentStatus::PAID()->value) ? 0 : $payment->amount_due,
            'payment_notes' => $response->notes ?? null,
        ]);

        // Only process order status updates if the order is in pending status
        if ($payment->order->status->value !== OrderStatus::PENDING()->value) {
            return;
        }

        if ($response->success && $response->status === PaymentStatus::PAID()->value) {
            Log::info('Payment successful, updating order status', [
                'order_id' => $payment->order->id,
                'order_number' => $payment->order->order_number,
            ]);
            $payment->order->update([
                'status' => OrderStatus::PAID()->value,
                'paid_at' => now(),
            ]);
        } elseif ($response->status === PaymentStatus::FAILED()->value) {
            Log::info('Payment successful, updating order status', [
                'order_id' => $payment->order->id,
                'order_number' => $payment->order->order_number,
            ]);
            $payment->order->update([
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
