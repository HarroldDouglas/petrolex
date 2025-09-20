<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Get(
 *     path="/api/my/orders",
 *     operationId="getMyOrders",
 *     summary="Récupérer mes commandes",
 *     description="Retourne la liste paginée des commandes du client authentifié avec filtres optionnels",
 *     tags={"Commandes"},
 *     security={{"bearerAuth": {}}},
 *
 *     @OA\Parameter(
 *         name="order_number",
 *         in="query",
 *         required=false,
 *         description="Numéro de commande à rechercher",
 *
 *         @OA\Schema(type="string", maxLength=255)
 *     ),
 *
 *     @OA\Parameter(
 *         name="status",
 *         in="query",
 *         required=false,
 *         description="Statut de la commande",
 *
 *         @OA\Schema(
 *             type="string",
 *             enum={"pending", "confirmed", "preparing", "ready", "in_delivery", "delivered", "cancelled"}
 *         )
 *     ),
 *
 *     @OA\Parameter(
 *         name="delivery_type",
 *         in="query",
 *         required=false,
 *         description="Type de livraison",
 *
 *         @OA\Schema(
 *             type="string",
 *             enum={"delivery", "pickup"}
 *         )
 *     ),
 *
 *     @OA\Parameter(
 *         name="payment_method",
 *         in="query",
 *         required=false,
 *         description="Méthode de paiement",
 *
 *         @OA\Schema(
 *             type="string",
 *             enum={"cash", "mobile_money", "bank_transfer"}
 *         )
 *     ),
 *
 *     @OA\Parameter(
 *         name="per_page",
 *         in="query",
 *         required=false,
 *         description="Nombre d'éléments par page (1-100)",
 *
 *         @OA\Schema(type="integer", minimum=1, maximum=100, default=10)
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Mes commandes récupérées avec succès",
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(
 *                 property="_metadata",
 *                 type="object",
 *                 @OA\Property(property="success", type="boolean", example=true),
 *                 @OA\Property(property="message", type="string", example="Commandes récupérées avec succès")
 *             ),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(
 *                     property="orders",
 *                     type="array",
 *
 *                     @OA\Items(
 *                         type="object",
 *
 *                         @OA\Property(property="id", type="integer", example=1),
 *                         @OA\Property(property="order_number", type="string", example="ORD-20241201-001"),
 *                         @OA\Property(property="status", type="string", example="confirmed"),
 *                         @OA\Property(property="delivery_type", type="string", example="delivery"),
 *                         @OA\Property(property="total_amount", type="number", format="float", example=25.50)
 *                     )
 *                 ),
 *                 @OA\Property(
 *                     property="pagination",
 *                     type="object",
 *                     @OA\Property(property="current_page", type="integer", example=1),
 *                     @OA\Property(property="per_page", type="integer", example=10),
 *                     @OA\Property(property="total", type="integer", example=42)
 *                 )
 *             )
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
 *         response=500,
 *         description="Erreur serveur",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 */
