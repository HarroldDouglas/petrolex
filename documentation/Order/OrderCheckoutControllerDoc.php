<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="OrderCheckoutItem",
 *     type="object",
 *     required={"product_id", "quantity", "option", "unit_price"},
 *
 *     @OA\Property(property="product_id", type="integer", example=5, description="ID du produit"),
 *     @OA\Property(property="quantity", type="integer", example=2, description="Quantité commandée"),
 *     @OA\Property(property="option", type="string", example="bottle_with_content", description="Option du produit"),
 *     @OA\Property(property="unit_price", type="number", format="float", example=8500, description="Prix unitaire")
 * )
 *
 * @OA\Schema(
 *     schema="OrderCheckoutRequest",
 *     type="object",
 *     required={"customer_id", "distribution_center_id", "items", "payment_method"},
 *
 *     @OA\Property(property="customer_id", type="integer", example=12, description="ID du client"),
 *     @OA\Property(property="distribution_center_id", type="integer", example=3, description="ID du centre de distribution"),
 *     @OA\Property(
 *         property="items",
 *         type="array",
 *         description="Liste des articles de la commande",
 *
 *         @OA\Items(ref="#/components/schemas/OrderCheckoutItem")
 *     ),
 *
 *     @OA\Property(property="payment_method", type="string", example="mobile_money", description="Méthode de paiement"),
 *     @OA\Property(property="delivery_address_id", type="integer", example=7, description="ID de l'adresse de livraison"),
 *     @OA\Property(property="comments", type="string", example="Livrer rapidement", description="Commentaires optionnels")
 * )
 *
 * @OA\Schema(
 *     schema="OrderCheckoutResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiResponse"),
 *         @OA\Schema(
 *
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="order_id", type="integer", example=123),
 *                 @OA\Property(property="order_number", type="string", example="ORD-20250822-001"),
 *                 @OA\Property(property="payment_url", type="string", example="https://payment-gateway.com/pay/abc123")
 *             )
 *         )
 *     }
 * )
 *
 * @OA\Post(
 *     path="/api/orders/checkout",
 *     summary="Passer une commande (checkout)",
 *     description="Crée une nouvelle commande et initie le paiement.",
 *     operationId="api.orders.checkout",
 *     tags={"Commandes"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\RequestBody(
 *         required=true,
 *
 *         @OA\JsonContent(ref="#/components/schemas/OrderCheckoutRequest")
 *     ),
 *
 *     @OA\Response(
 *         response=201,
 *         description="Commande créée avec succès",
 *
 *         @OA\JsonContent(ref="#/components/schemas/OrderCheckoutResponse")
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
 *         response=422,
 *         description="Erreur de validation",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 */
class OrderCheckoutControllerDoc {}
