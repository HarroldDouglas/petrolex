<?php

namespace App\Http\Api\Controllers;

use OpenApi\Annotations as OA;

/**
 * @OA\Get(
 *     path="/api/orders/{order}",
 *     operationId="getOrderDetails",
 *     summary="Récupérer les détails complets d'une commande",
 *     description="Retourne tous les détails d'une commande spécifique: informations de base, client, adresse de livraison, livreur, articles, paiement, suivi, etc.",
 *     tags={"Commandes"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="order",
 *         in="path",
 *         required=true,
 *         description="ID de la commande",
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Détails de la commande récupérés avec succès",
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(
 *                 property="_metadata",
 *                 type="object",
 *                 @OA\Property(property="success", type="boolean", example=true),
 *                 @OA\Property(property="message", type="string", example="Détails de la commande récupérés avec succès")
 *             ),
 *             @OA\Property(
 *                 property="data",
 *                 ref="#/components/schemas/OrderDetailsData"
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=401,
 *         description="Non authentifié",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=403,
 *         description="Non autorisé à voir cette commande",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=404,
 *         description="Commande non trouvée",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 */
class GetOrderDetailsControllerDoc {}

/**
 * Note: OrderDetailsData schema is now defined in BaseSchemas.php to avoid duplication
 *
 * @OA\Schema(
 *     schema="OrderItemDetails",
 *     title="OrderItemDetails",
 *     description="Détails d'un article dans une commande",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="quantity", type="integer", example=2),
 *     @OA\Property(property="unit_price", type="number", format="float", example=750.00),
 *     @OA\Property(property="total_price", type="number", format="float", example=1500.00),
 *     @OA\Property(property="bottle_type", type="string", nullable=true, example="bottle_with_content"),
 *     @OA\Property(property="bottle_type_label", type="string", nullable=true, example="Nouvelle bouteille"),
 *     @OA\Property(
 *         property="product_category",
 *         type="object",
 *         nullable=true,
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="Bouteilles 19L"),
 *         @OA\Property(property="product_type", type="string", example="bottle"),
 *         @OA\Property(property="product_type_label", type="string", example="Bouteille")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="BottleMovement",
 *     title="BottleMovement",
 *     description="Mouvement de consigne de bouteille",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="bottle_id", type="integer", example=1),
 *     @OA\Property(property="movement_type", type="string", example="deposit"),
 *     @OA\Property(property="movement_type_label", type="string", example="Consigne"),
 *     @OA\Property(property="quantity", type="integer", example=2),
 *     @OA\Property(property="unit_deposit_amount", type="number", format="float", example=2000.00),
 *     @OA\Property(property="total_deposit_amount", type="number", format="float", example=4000.00),
 *     @OA\Property(property="created_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="RefundDetails",
 *     title="RefundDetails",
 *     description="Détails d'un remboursement",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="amount", type="number", format="float", example=500.00),
 *     @OA\Property(property="reason", type="string", example="Produit défectueux"),
 *     @OA\Property(property="status", type="string", example="processed"),
 *     @OA\Property(property="status_label", type="string", example="Traité"),
 *     @OA\Property(property="processed_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="DeliveryTracking",
 *     title="DeliveryTracking",
 *     description="Suivi de livraison en temps réel",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="status", type="string", example="processing"),
 *     @OA\Property(property="status_label", type="string", example="En cours"),
 *     @OA\Property(property="current_latitude", type="number", format="float", nullable=true, example=3.850),
 *     @OA\Property(property="current_longitude", type="number", format="float", nullable=true, example=11.500),
 *     @OA\Property(property="estimated_arrival", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="started_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="completed_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
