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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        /** @var OrderPayment */
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

        $status     = $response->status;
        $amountDue  = $payment->amount_due;
        $now        = now();

        $updateData = [
            'payment_status'       => $status,
            'payment_notes'        => $response->notes ?? null,
            'gateway_response'     => $response,
            'transaction_reference'=> $response->transactionReference,
            'payment_date'         => in_array($status, [PaymentStatus::PAID()->value, PaymentStatus::FAILED()->value]) ? $now : null,
            'amount_paid'          => $status === PaymentStatus::PAID()->value ? $amountDue : 0,
            'amount_due'           => $status === PaymentStatus::PAID()->value ? 0 : $amountDue,
        ];

        // Clean out null values (like transaction_reference when missing)
        $updateData = array_filter($updateData, fn($value) => !is_null($value));

        $this->orderPaymentRepository->update($payment, $updateData);

        Log::info('Ready to update order', [
            'order_id'         => $payment->order->id,
            'order_number'     => $payment->order->order_number,
            'response_status'  => $status,
            'response_success' => $response->success,
        ]);

        $orderUpdateData = match ($status) {
            PaymentStatus::PAID()->value => [
                'status'  => OrderStatus::PAID()->value,
                'paid_at' => $now,
            ],
            PaymentStatus::FAILED()->value => [
                'status' => OrderStatus::FAILED()->value,
            ],
            default => null
        };

        if ($orderUpdateData && $response->success || $status === PaymentStatus::FAILED()->value) {
            Log::info('Updating order status via OrderService', [
                'order_id'     => $payment->order->id,
                'order_number' => $payment->order->order_number,
                'new_status'   => $orderUpdateData['status'],
            ]);

            $this->orderService->update($payment->order, $orderUpdateData);
        }
    }


    private function schedulePaymentCallback(OrderPayment $payment, PaymentResponse $response): void
    {
        $referenceId = $response->transactionReference ?? $payment->payment_reference;

        dispatch(new \App\Jobs\VerifyPaymentStatusJob(
            $referenceId,
            $payment->payment_method,
            $this,
            1,
            $this->orderPaymentRepository
        ))->delay(now()->addSeconds(30));
    }
}
