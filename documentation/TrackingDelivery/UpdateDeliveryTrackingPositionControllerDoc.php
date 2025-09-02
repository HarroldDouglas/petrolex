<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Patch(
 *     path="/api/tracking/delivery/{orderId}/position",
 *     summary="Mettre à jour la position de suivi de livraison",
 *     description="Met à jour la position géographique d'une livraison.",
 *     operationId="api.tracking.delivery.position.update",
 *     tags={"Livraison"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="orderId",
 *         in="path",
 *         required=true,
 *         description="L'ID de la commande de la livraison pour mettre à jour la position",
 *
 *         @OA\Schema(type="integer")
 *     ),
 *
 *     @OA\RequestBody(
 *         required=true,
 *         description="Données de position et de suivi en temps réel",
 *
 *         @OA\JsonContent(
 *             required={"driver_lat", "driver_lng"},
 *
 *             @OA\Property(
 *                 property="driver_lat",
 *                 type="number",
 *                 format="float",
 *                 minimum=-90,
 *                 maximum=90,
 *                 description="Latitude actuelle du livreur",
 *                 example=3.8495
 *             ),
 *             @OA\Property(
 *                 property="driver_lng",
 *                 type="number",
 *                 format="float",
 *                 minimum=-180,
 *                 maximum=180,
 *                 description="Longitude actuelle du livreur",
 *                 example=11.5035
 *             ),
 *             @OA\Property(
 *                 property="current_speed",
 *                 type="number",
 *                 format="float",
 *                 minimum=0,
 *                 description="Vitesse actuelle en km/h (optionnel)",
 *                 example=35.5
 *             ),
 *             @OA\Property(
 *                 property="progress_percentage",
 *                 type="number",
 *                 format="float",
 *                 minimum=0,
 *                 maximum=100,
 *                 description="Pourcentage de progression de la livraison (optionnel)",
 *                 example=75.2
 *             ),
 *             @OA\Property(
 *                 property="distance_remaining",
 *                 type="number",
 *                 format="float",
 *                 minimum=0,
 *                 description="Distance restante en kilomètres (optionnel)",
 *                 example=2.8
 *             ),
 *             @OA\Property(
 *                 property="estimated_duration",
 *                 type="integer",
 *                 minimum=0,
 *                 description="Durée estimée en minutes (optionnel)",
 *                 example=8
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Opération réussie",
 *
 *         @OA\JsonContent(
 *             allOf={
 *
 *                 @OA\Schema(ref="#/components/schemas/ApiResponse"),
 *                 @OA\Schema(
 *
 *                     @OA\Property(property="data", ref="#/components/schemas/DeliveryTrackingData"),
 *                     @OA\Property(property="message", type="string", example="Position de suivi de livraison mise à jour avec succès.")
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
 *         response=404,
 *         description="Livraison introuvable",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=422,
 *         description="Erreur de validation",
 *
 *         @OA\JsonContent(
 *             allOf={
 *
 *                 @OA\Schema(ref="#/components/schemas/ApiResponse"),
 *                 @OA\Schema(
 *
 *                     @OA\Property(property="success", type="boolean", example=false),
 *                     @OA\Property(property="message", type="string", example="Erreur de validation."),
 *                     @OA\Property(
 *                         property="data",
 *                         type="object",
 *                         @OA\Property(
 *                             property="driver_lat",
 *                             type="array",
 *
 *                             @OA\Items(type="string", example="Le champ driver_lat est requis.")
 *                         ),
 *
 *                         @OA\Property(
 *                             property="driver_lng",
 *                             type="array",
 *
 *                             @OA\Items(type="string", example="Le champ driver_lng est requis.")
 *                         )
 *                     )
 *                 )
 *             }
 *         )
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
class UpdateDeliveryTrackingPositionControllerDoc {}
