<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="DeliveryType",
 *     title="Delivery Type",
 *     description="Represents a single delivery type",
 *
 *     @OA\Property(property="value", type="string", description="The unique identifier for the delivery type", example="normal"),
 *     @OA\Property(property="label", type="string", description="The human-readable label for the delivery type", example="Standard"),
 *     @OA\Property(property="fee", type="integer", description="The delivery fee in cents", example="500"),
 *     @OA\Property(property="description", type="string", description="The delivery description", example="Within 24 hours")
 * )
 *
 * @OA\Schema(
 *     schema="DeliveryTypesResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiResponse"),
 *         @OA\Schema(
 *
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *
 *                 @OA\Items(ref="#/components/schemas/DeliveryType")
 *             ),
 *
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Delivery types retrieved successfully."
 *             )
 *         )
 *     }
 * )
 *
 * @OA\Get(
 *     path="/api/delivery-types",
 *     summary="Récupérer tous les types de livraison",
 *     description="Récupérer la liste de tous les types de livraison disponibles.",
 *     operationId="api.delivery-types.index",
 *     tags={"Livraison"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Response(
 *         response=200,
 *         description="Liste récupérée avec succès",
 *
 *         @OA\JsonContent(ref="#/components/schemas/DeliveryTypesResponse")
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
class GetDeliveryTypesControllerDoc {}
