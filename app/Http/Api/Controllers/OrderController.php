<?php

namespace App\Http\Api\Controllers;

use App\Http\Api\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\PaymentService;
use App\Enums\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function __construct(
        private PaymentService $paymentService
    ) {}

    /**
     * Créer une commande ET initier le paiement
     */
    public function processCheckout(Request $request): ApiResponse
    {
        $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'items' => ['required', 'array'],
            'payment_method' => ['required', 'string', 'in:orange_money,mtn_money,credit_card,cash'],
            'delivery_address_id' => ['required', 'exists:customer_delivery_addresses,id'],
        ]);

        return DB::transaction(function() use ($request) {
            // 1. Créer la commande (Logique à implémenter ou à appeler d'un service existant)
            // Pour l'exemple, je vais créer une commande simple
            $order = Order::create([
                'customer_id' => $request->customer_id,
                'delivery_address_id' => $request->delivery_address_id,
                'total_amount' => 100.00, // Exemple: à calculer à partir des items
                'status' => 'pending', // Initial status
            ]);

            // 2. Initier le paiement
            $payment = $this->paymentService->initiatePayment(
                $order,
                PaymentMethod::from($request->payment_method)
            );

            return ApiResponse::success(
                data: [
                    'order_id' => $order->id,
                    'payment_reference' => $payment->payment_reference,
                    'payment_url' => $payment->payment_url,
                    'status' => $payment->payment_status->value,
                ],
                message: 'Commande créée et paiement initié avec succès.',
                statusCode: 201
            );
        });
    }

    /**
     * Callback universel pour tous les gateways
     */
    public function paymentCallback(Request $request): ApiResponse
    {
        $request->validate([
            'payment_reference' => ['required', 'string'],
            'status' => ['required', 'string'],
            'amount' => ['required', 'numeric'],
            // Ajoutez d'autres validations si nécessaire pour les données de callback
        ]);

        $this->paymentService->handleCallback(
            $request->payment_reference,
            $request->all()
        );

        return ApiResponse::success(message: 'Callback de paiement traité avec succès.');
    }
}
