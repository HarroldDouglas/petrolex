<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Get(
 *     path="/api/customers/{customer}/orders",
 *     summary="Lister les commandes d'un client",
 *     description="Récupère la liste paginée des commandes d'un client avec des filtres optionnels.",
 *     operationId="api.customers.orders.index",
 *     tags={"Clients"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="customer",
 *         in="path",
 *         required=true,
 *         description="ID du client",
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Parameter(
 *         name="order_number",
 *         in="query",
 *         required=false,
 *         description="Numéro de commande à filtrer",
 *
 *         @OA\Schema(type="string", example="ORD-123456")
 *     ),
 *
 *     @OA\Parameter(
 *         name="status",
 *         in="query",
 *         required=false,
 *         description="Statut de la commande à filtrer",
 *
 *         @OA\Schema(type="string", example="delivered")
 *     ),
 *
 *     @OA\Parameter(
 *         name="delivery_type",
 *         in="query",
 *         required=false,
 *         description="Type de livraison à filtrer",
 *
 *         @OA\Schema(type="string", example="fast")
 *     ),
 *
 *     @OA\Parameter(
 *         name="payment_method",
 *         in="query",
 *         required=false,
 *         description="Méthode de paiement à filtrer",
 *
 *         @OA\Schema(type="string", example="mobile_money")
 *     ),
 *
 *     @OA\Parameter(
 *         name="per_page",
 *         in="query",
 *         required=false,
 *         description="Nombre d'éléments par page (pagination)",
 *
 *         @OA\Schema(type="integer", example=10)
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Liste paginée des commandes du client",
 *
 *         @OA\JsonContent(ref="#/components/schemas/CustomerOrdersResponse")
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
 *         description="Client non trouvé",
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
 *
 * @OA\Schema(
 *     schema="CustomerOrdersResponse",
 *     type="object",
 *
 *     @OA\Property(
 *         property="data",
 *         type="array",
 *
 *         @OA\Items(ref="#/components/schemas/Order")
 *     ),
 *
 *     @OA\Property(
 *         property="meta",
 *         type="object",
 *         @OA\Property(property="current_page", type="integer", example=1),
 *         @OA\Property(property="last_page", type="integer", example=10),
 *         @OA\Property(property="per_page", type="integer", example=10),
 *         @OA\Property(property="total", type="integer", example=100)
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="Order",
 *     type="object",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="order_number", type="string", example="ORD-123456"),
 *     @OA\Property(property="status", type="string", example="delivered"),
 *     @OA\Property(property="delivery_type", type="string", example="fast"),
 *     @OA\Property(property="payment_method", type="string", example="mobile_money"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-06-01T12:00:00Z")
 * )
 */
class GetCustomerOrdersControllerDoc {}
