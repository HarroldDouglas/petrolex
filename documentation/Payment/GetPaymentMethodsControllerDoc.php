<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="PaymentMethod",
 *     title="Payment Method",
 *     description="Represents a single payment method",
 *
 *     @OA\Property(property="value", type="string", description="The unique identifier for the payment method", example="credit_card"),
 *     @OA\Property(property="label", type="string", description="The human-readable label for the payment method", example="Carte de Crédit")
 * )
 *
 * @OA\Schema(
 *     schema="PaymentMethodsResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiResponse"),
 *         @OA\Schema(
 *
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *
 *                 @OA\Items(ref="#/components/schemas/PaymentMethod")
 *             ),
 *
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Payment methods retrieved successfully."
 *             )
 *         )
 *     }
 * )
 *
 * @OA\Get(
 *     path="/api/payment-methods",
 *     summary="Récupérer toutes les méthodes de paiement",
 *     description="Récupérer la liste de toutes les méthodes de paiement disponibles.",
 *     operationId="api.payment-methods.index",
 *     tags={"Paiement"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Response(
 *         response=200,
 *         description="Liste récupérée avec succès",
 *
 *         @OA\JsonContent(ref="#/components/schemas/PaymentMethodsResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=401,
 *         description="Non autorisé",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=500,
 *         description="Erreur interne du serveur",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 */
class GetPaymentMethodsControllerDoc {}
