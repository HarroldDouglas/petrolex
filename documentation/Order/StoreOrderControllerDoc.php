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
 *     @OA\Property(property="payment_method", type="string", example="cash", description="Méthode de paiement", enum={"cash", "mobile_money", "card"}),
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
