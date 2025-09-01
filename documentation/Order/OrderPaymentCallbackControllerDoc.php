<?php

namespace App\Docs\Controllers;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="OrderPaymentCallbackRequest",
 *     type="object",
 *     required={"order_id", "status", "transaction_reference"},
 *
 *     @OA\Property(property="order_id", type="integer", example=123, description="ID de la commande"),
 *     @OA\Property(property="status", type="string", example="paid", description="Statut du paiement (paid, failed, pending, etc.)"),
 *     @OA\Property(property="transaction_reference", type="string", example="TXN-123456789", description="Référence de la transaction"),
 *     @OA\Property(property="payment_method", type="string", example="mobile_money", description="Méthode de paiement"),
 *     @OA\Property(property="amount", type="number", format="float", example=23000, description="Montant payé"),
 *     @OA\Property(
 *         property="meta",
 *         type="object",
 *         description="Données additionnelles du callback",
 *
 *         @OA\AdditionalProperties(type="string")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="OrderPaymentCallbackResponse",
 *     type="object",
 *
 *     @OA\Property(
 *         property="_metadata",
 *         type="object",
 *         @OA\Property(property="success", type="boolean", example=true),
 *         @OA\Property(property="message", type="string", example="Callback de paiement traité avec succès")
 *     ),
 *     @OA\Property(
 *         property="data",
 *         type="object",
 *         @OA\Property(property="order_id", type="integer", example=123),
 *         @OA\Property(property="status", type="string", example="paid")
 *     )
 * )
 *
 * @OA\Post(
 *     path="/api/orders/payment-callback",
 *     summary="Callback de paiement d'une commande",
 *     description="Traite le callback de paiement envoyé par le prestataire de paiement.",
 *     operationId="api.orders.paymentCallback",
 *     tags={"Commandes"},
 *
 *     @OA\RequestBody(
 *         required=true,
 *
 *         @OA\JsonContent(ref="#/components/schemas/OrderPaymentCallbackRequest")
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Callback traité avec succès",
 *
 *         @OA\JsonContent(ref="#/components/schemas/OrderPaymentCallbackResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=422,
 *         description="Erreur de validation",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 */
class OrderPaymentCallbackControllerDoc {}
