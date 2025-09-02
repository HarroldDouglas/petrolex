<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Post(
 *     path="/api/tracking/delivery",
 *     summary="Créer un nouveau enregistrement de suivi de livraison",
 *     description="Initialise un nouvel enregistrement de suivi pour une livraison.",
 *     operationId="api.tracking.delivery.create",
 *     tags={"Livraison"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\RequestBody(
 *         required=true,
 *         description="Données pour créer un nouvel enregistrement de suivi de livraison",
 *
 *         @OA\JsonContent(
 *             required={"order_id"},
 *
 *             @OA\Property(property="order_id", type="integer", description="L'ID de la commande à suivre", example=1)
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=201,
 *         description="Suivi de livraison créé avec succès",
 *
 *         @OA\JsonContent(
 *             allOf={
 *
 *                 @OA\Schema(ref="#/components/schemas/ApiResponse"),
 *                 @OA\Schema(
 *
 *                     @OA\Property(property="data", ref="#/components/schemas/DeliveryTrackingData"),
 *                     @OA\Property(property="message", type="string", example="Suivi de livraison créé avec succès.")
 *                 )
 *             }
 *         )
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
 *         response=422,
 *         description="Erreur de validation",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur interne",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 */
class CreateDeliveryTrackingControllerDoc {}
