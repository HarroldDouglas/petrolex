<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Get(
 *     path="/api/customers/{customer}/orders",
 *     summary="Obtenir les commandes d'un client spécifique",
 *     tags={"Clients"},
 *
 *     @OA\Parameter(
 *         name="customer",
 *         in="path",
 *         required=true,
 *         description="L'ID du client",
 *
 *         @OA\Schema(type="integer")
 *     ),
 *
 *     @OA\Parameter(
 *         name="order_number",
 *         in="query",
 *         description="Filtrer par numéro de commande",
 *
 *         @OA\Schema(type="string")
 *     ),
 *
 *     @OA\Parameter(
 *         name="status",
 *         in="query",
 *         description="Filtrer par statut de commande",
 *
 *         @OA\Schema(ref="#/components/schemas/OrderStatus")
 *     ),
 *
 *     @OA\Parameter(
 *         name="ticket_url",
 *         in="query",
 *         description="Filtrer par URL du ticket",
 *
 *         @OA\Schema(type="string")
 *     ),
 *
 *      @OA\Parameter(
 *         name="delivery_type",
 *         in="query",
 *         description="Filtrer par type de livraison",
 *
 *         @OA\Schema(ref="#/components/schemas/DeliveryType")
 *     ),
 *
 *      @OA\Parameter(
 *         name="payment_method",
 *         in="query",
 *         description="Filtrer par méthode de paiement",
 *
 *         @OA\Schema(ref="#/components/schemas/PaymentMethod")
 *     ),
 *
 *     @OA\Parameter(
 *         name="per_page",
 *         in="query",
 *         description="Nombre d'éléments par page",
 *
 *         @OA\Schema(type="integer", default=10)
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Opération réussie",
 *
 *         @OA\JsonContent(ref="#/components/schemas/CustomerOrdersResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=404,
 *         description="Client introuvable"
 *     )
 * )
 */
