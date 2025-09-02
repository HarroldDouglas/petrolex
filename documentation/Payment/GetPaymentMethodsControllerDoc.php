<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="PaymentMethod",
 *     title="Méthode de Paiement",
 *     description="Représente une méthode de paiement",
 *
 *     @OA\Property(
 *         property="value",
 *         type="string",
 *         description="L'identifiant unique de la méthode de paiement",
 *         example="credit_card"
 *     ),
 *     @OA\Property(
 *         property="label",
 *         type="string",
 *         description="Le libellé lisible de la méthode de paiement",
 *         example="Carte de Crédit"
 *     )
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
 *                 example="Méthodes de paiement récupérées avec succès."
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
