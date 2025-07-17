<?php

namespace App\Services;

use App\Contracts\PaymentGateway;
use App\Models\Order;
use App\Models\OrderPayment;
use App\DTOs\PaymentCallbackData;
use App\DTOs\PaymentResponse;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\OrderStatus;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(
        private PaymentGatewayFactory $gatewayFactory
    ) {}

    public function initiatePayment(Order $order, PaymentMethod $method): OrderPayment
    {
        // 1. Créer OrderPayment
        $payment = $this->createOrderPayment($order, $method);

        // 2. Obtenir le gateway approprié
        $gateway = $this->gatewayFactory->create($method->value);

        // 3. Initier le paiement
        $response = $gateway->initiatePayment($payment);

        // 4. Mettre à jour avec la réponse
        $this->updatePaymentFromResponse($payment, $response);

        return $payment;
    }

    public function handleCallback(string $paymentReference, array $callbackData): void
    {
        DB::transaction(function () use ($paymentReference, $callbackData) {
            // 1. Trouver le paiement
            $payment = $this->findPaymentByReference($paymentReference);

            // 2. Obtenir le gateway approprié
            $gateway = $this->gatewayFactory->create($payment->payment_method->value);

            // 3. Traiter le callback
            $callbackDto = new PaymentCallbackData(
                transactionReference: $callbackData['transactionReference'] ?? $paymentReference,
                status: $callbackData['status'],
                amount: $callbackData['amount'],
                rawData: $callbackData
            );
            $response = $gateway->handleCallback($callbackDto);

            // 4. Mettre à jour le paiement et la commande
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
            'transaction_reference' => $response->transactionReference ?? $payment->transaction_reference,
            'payment_url' => $response->paymentUrl ?? $payment->payment_url,
            'gateway_response' => $response->gatewayResponse ?? $payment->gateway_response,
            'payment_date' => ($response->status === PaymentStatus::PAID()->value) ? now() : null,
            'amount_paid' => ($response->status === PaymentStatus::PAID()->value) ? $payment->amount_due : $payment->amount_paid,
            'amount_due' => ($response->status === PaymentStatus::PAID()->value) ? 0 : $payment->amount_due,
        ]);
    }

    private function findPaymentByReference(string $reference): OrderPayment
    {
        $payment = OrderPayment::where('payment_reference', $reference)->first();

        if (! $payment) {
            throw (new \Illuminate\Database\Eloquent\ModelNotFoundException())->setModel(OrderPayment::class, [$reference]);
        }

        return $payment;
    }

    private function processPaymentResponse(OrderPayment $payment, PaymentResponse $response): void
    {
        $payment->update([
            'payment_status' => $response->status,
            'transaction_reference' => $response->transactionReference ?? $payment->transaction_reference,
            'gateway_response' => $response->gatewayResponse ?? $payment->gateway_response,
            'payment_date' => ($response->status === PaymentStatus::PAID()->value) ? now() : null,
            'amount_paid' => ($response->status === PaymentStatus::PAID()->value) ? $payment->amount_due : $payment->amount_paid,
            'amount_due' => ($response->status === PaymentStatus::PAID()->value) ? 0 : $payment->amount_due,
        ]);

        if ($response->success && $response->status === PaymentStatus::PAID()->value) {
            $payment->order->update([
                'status' => OrderStatus::PAID()->value, // Assuming OrderStatus enum exists
            ]);
        } elseif ($response->status === PaymentStatus::FAILED()->value) {
            $payment->order->update([
                'status' => OrderStatus::FAILED()->value, // Assuming OrderStatus enum exists
            ]);
        }
    }
}
