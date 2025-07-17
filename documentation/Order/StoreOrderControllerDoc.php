<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="OrderItemRequest",
 *     required={
 *         "product_category_id",
 *         "quantity"
 *     },
 *
 *     @OA\Property(property="product_category_id", type="integer", example=1, description="ID de la catégorie de produit"),
 *     @OA\Property(property="quantity", type="integer", example=2, description="Quantité de l'article"),
 *     @OA\Property(property="option", type="string", example="bottle_with_content", nullable=true, description="Option pour les bouteilles (e.g., content_only, bottle_with_content)", enum={"content_only", "bottle_with_content"}),
 * )
 *
 * @OA\Schema(
 *     schema="StoreOrderRequest",
 *     required={
 *         "customer_id",
 *         "delivery_address_id",
 *         "distribution_center_id",
 *         "delivery_type",
 *         "payment_method",
 *         "items"
 *     },
 *
 *     @OA\Property(property="customer_id", type="integer", example=1, description="ID du client"),
 *     @OA\Property(property="delivery_address_id", type="integer", example=1, description="ID de l'adresse de livraison"),
 *     @OA\Property(property="distribution_center_id", type="integer", example=1, description="ID du centre de distribution"),
 *     @OA\Property(property="delivery_type", type="string", example="delivery", description="Type de livraison", enum={"delivery", "pickup"}),
 *     @OA\Property(property="payment_method", type="string", example="mobile_money", description="Méthode de paiement", enum={"mobile_money", "credit_card"}),
 *     @OA\Property(
 *         property="items",
 *         type="array",
 *
 *         @OA\Items(ref="#/components/schemas/OrderItemRequest"),
 *         description="Liste des articles de la commande"
 *     ),
 * )
 *
 * @OA\Schema(
 *     schema="OrderItemResource",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="order_id", type="integer", example=1),
 *     @OA\Property(property="product_category_id", type="integer", example=1),
 *     @OA\Property(property="quantity", type="integer", example=2),
 *     @OA\Property(property="unit_price", type="number", format="float", example=5000.00),
 *     @OA\Property(property="total_price", type="number", format="float", example=10000.00),
 *     @OA\Property(property="option", type="string", example="bottle_with_content", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 * )
 *
 * @OA\Schema(
 *     schema="DistributionCenterResource",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Centre Principal"),
 *     @OA\Property(property="country", type="string", example="Cameroun"),
 *     @OA\Property(property="city", type="string", example="Douala"),
 *     @OA\Property(property="neighborhood", type="string", example="Bonanjo"),
 *     @OA\Property(property="address", type="string", example="123 Rue Principale, Douala"),
 *     @OA\Property(property="description", type="string", example="Centre de distribution principal avec toutes les commodités"),
 *     @OA\Property(property="latitude", type="number", format="float", example=4.0511),
 *     @OA\Property(property="longitude", type="number", format="float", example=9.7679),
 *     @OA\Property(property="phone", type="string", example="+237612345678"),
 *     @OA\Property(property="email", type="string", format="email", example="centre.principal@petrolex.cm"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="storage_capacity", type="integer", nullable=true, example=1000),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 * )
 *
 * @OA\Schema(
 *     schema="OrderResource",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="order_number", type="string", example="ORDER-123456789"),
 *     @OA\Property(property="customer_id", type="integer", example=1),
 *     @OA\Property(property="customer", ref="#/components/schemas/CustomerData"),
 *     @OA\Property(property="delivery_address_id", type="integer", example=1),
 *     @OA\Property(property="delivery_address", ref="#/components/schemas/DeliveryAddress"),
 *     @OA\Property(property="distribution_center_id", type="integer", example=1),
 *     @OA\Property(property="distribution_center", ref="#/components/schemas/DistributionCenterResource"),
 *     @OA\Property(property="delivery_type", type="string", example="normal"),
 *     @OA\Property(property="payment_method", type="string", example="mobile_money"),
 *     @OA\Property(property="total_amount", type="number", format="float", example=23500.00),
 *     @OA\Property(property="status", type="string", example="pending"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(
 *         property="items",
 *         type="array",
 *
 *         @OA\Items(ref="#/components/schemas/OrderItemResource")
 *     ),
 * )
 *
 * @OA\Schema(
 *     schema="StoreOrderResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiResponse"),
 *         @OA\Schema(
 *
 *             @OA\Property(
 *                 property="data",
 *                 ref="#/components/schemas/OrderResource"
 *             ),
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Commande créée avec succès"
 *             )
 *         )
 *     }
 * )
 *
 * @OA\Post(
 *     path="/api/orders",
 *     summary="Créer une nouvelle commande",
 *     description="Permet de créer une nouvelle commande avec ses articles.",
 *     operationId="api.orders.store",
 *     tags={"Commandes"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\RequestBody(
 *         required=true,
 *         description="Données de la commande à créer",
 *
 *         @OA\JsonContent(ref="#/components/schemas/StoreOrderRequest")
 *     ),
 *
 *     @OA\Response(
 *         response=201,
 *         description="Commande créée avec succès",
 *
 *         @OA\JsonContent(ref="#/components/schemas/StoreOrderResponse")
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
 *         description="Ressource non trouvée (client, adresse de livraison, centre de distribution, produit)",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=422,
 *         description="Erreur de validation ou stock insuffisant",
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
class StoreOrderControllerDoc {}