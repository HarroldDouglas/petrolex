<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="PaymentCallbackRequest",
 *     type="object",
 *     required={"application", "app_transaction_ref", "operator_transaction_ref", "transaction_ref", "transaction_type", "transaction_amount", "transaction_fees", "transaction_currency", "transaction_operator", "transaction_status", "transaction_reason", "transaction_message", "customer_phone_number", "signature"},
 *
 *     @OA\Property(property="application", type="string", example="PETROLEX", description="Nom de l'application"),
 *     @OA\Property(property="app_transaction_ref", type="string", example="107", description="Référence de transaction de l'application (Order ID)"),
 *     @OA\Property(property="operator_transaction_ref", type="string", example="OM_230920250211_107", description="Référence de transaction de l'opérateur"),
 *     @OA\Property(property="transaction_ref", type="string", example="TXN_PETROLEX_107_20250920021148", description="Référence de transaction unique"),
 *     @OA\Property(property="transaction_type", type="string", enum={"PAYIN", "PAYOUT"}, example="PAYIN", description="Type de transaction"),
 *     @OA\Property(property="transaction_amount", type="number", format="float", example=22300.0, description="Montant de la transaction"),
 *     @OA\Property(property="transaction_fees", type="number", format="float", example=100.0, description="Frais de transaction"),
 *     @OA\Property(property="transaction_currency", type="string", enum={"XAF", "EUR"}, example="XAF", description="Devise de la transaction"),
 *     @OA\Property(property="transaction_operator", type="string", enum={"MCP", "CM_MOMO", "CM_OM", "CARD"}, example="CM_OM", description="Opérateur de paiement"),
 *     @OA\Property(property="transaction_status", type="string", enum={"SUCCESS", "CANCELED", "CANCELLED", "FAILED"}, example="SUCCESS", description="Statut de la transaction"),
 *     @OA\Property(property="transaction_reason", type="string", example="Payment completed successfully", description="Raison de la transaction"),
 *     @OA\Property(property="transaction_message", type="string", example="Transaction processed successfully by Orange Money", description="Message de la transaction"),
 *     @OA\Property(property="customer_phone_number", type="string", example="677123456", description="Numéro de téléphone du client"),
 *     @OA\Property(property="signature", type="string", example="a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6", description="Signature de sécurité HMAC")
 * )
 *
 * @OA\Schema(
 *     schema="PaymentCallbackSuccessResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiResponse"),
 *         @OA\Schema(
 *
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="order_id", type="string", example="107"),
 *                 @OA\Property(property="transaction_status", type="string", example="SUCCESS"),
 *                 @OA\Property(property="processed_at", type="string", format="date-time", example="2025-01-20T14:30:00Z")
 *             )
 *         )
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="PaymentCallbackErrorResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiResponse"),
 *         @OA\Schema(
 *
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="order_id", type="string", example="107"),
 *                 @OA\Property(property="error", type="string", example="Order not found"),
 *                 @OA\Property(property="processed_at", type="string", format="date-time", example="2025-01-20T14:30:00Z")
 *             )
 *         )
 *     }
 * )
 *
 * @OA\Post(
 *     path="/api/payments/callback",
 *     summary="Callback de paiement",
 *     description="Endpoint pour recevoir les callbacks de paiement des passerelles de paiement externes. Aucune authentification requise car appelé par des services externes.",
 *     operationId="api.payments.callback",
 *     tags={"Paiement"},
 *
 *     @OA\RequestBody(
 *         required=true,
 *
 *         @OA\JsonContent(ref="#/components/schemas/PaymentCallbackRequest")
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Callback traité avec succès",
 *
 *         @OA\JsonContent(ref="#/components/schemas/PaymentCallbackSuccessResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=400,
 *         description="Erreur lors du traitement du callback",
 *
 *         @OA\JsonContent(ref="#/components/schemas/PaymentCallbackErrorResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=422,
 *         description="Erreur de validation des données du callback",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
 *     )
 * )
 */
class PaymentCallbackControllerDoc {}
