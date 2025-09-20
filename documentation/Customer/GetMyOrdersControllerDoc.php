<?php

/**
 * @OA\Get(
 *     path="/api/my/orders",
 *     summary="Récupérer mes commandes",
 *     description="Retourne la liste paginée des commandes du client authentifié avec filtres optionnels",
 *     operationId="api.my.orders.index",
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
 *         @OA\Schema(ref="#/components/schemas/OrderStatus")
 *     ),
 *
 *     @OA\Parameter(
 *         name="delivery_type",
 *         in="query",
 *         required=false,
 *         description="Type de livraison",
 *
 *         @OA\Schema(ref="#/components/schemas/DeliveryType")
 *     ),
 *
 *     @OA\Parameter(
 *         name="payment_method",
 *         in="query",
 *         required=false,
 *         description="Méthode de paiement",
 *
 *         @OA\Schema(ref="#/components/schemas/PaymentMethod")
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
 *         response=500,
 *         description="Erreur serveur",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 */
