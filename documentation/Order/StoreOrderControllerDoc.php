<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="CreateOrderItem",
 *     type="object",
 *     required={"product_category_id", "quantity", "unit_price"},
 *
 *     @OA\Property(property="product_category_id", type="integer", example=1, description="ID de la catégorie de produit"),
 *     @OA\Property(property="quantity", type="integer", minimum=1, maximum=100, example=2, description="Quantité commandée (1-100)"),
 *     @OA\Property(property="unit_price", type="number", format="float", example=3900.00, description="Prix unitaire du produit"),
 *     @OA\Property(property="option", type="string", nullable=true, enum={"content", "bottle_with_content"}, example="bottle_with_content", description="Option du produit: content (recharge seulement), bottle_with_content (bouteille pleine), ou null (accessoires)")
 * )
 *
 * @OA\Schema(
 *     schema="CreateOrderRequest",
 *     type="object",
 *     required={"delivery_address_id", "distribution_center_id", "delivery_type", "items", "delivery_fee", "total_amount"},
 *
 *     @OA\Property(property="delivery_address_id", type="integer", example=1, description="ID de l'adresse de livraison du client"),
 *     @OA\Property(property="distribution_center_id", type="integer", example=1, description="ID du centre de distribution"),
 *     @OA\Property(property="delivery_type", type="string", enum={"normal", "fast"}, example="normal", description="Type de livraison"),
 *     @OA\Property(
 *         property="items",
 *         type="array",
 *         description="Liste des articles de la commande - Exemple complet avec 3 items : 1 bouteille avec recharge, 1 bouteille pleine, 1 accessoire",
 *         minItems=1,
 *         example={{
 *             "product_category_id": 1,
 *             "quantity": 2,
 *             "unit_price": 3900.00,
 *             "option": "content"
 *         }, {
 *             "product_category_id": 1,
 *             "quantity": 1,
 *             "unit_price": 5000.00,
 *             "option": "bottle_with_content"
 *         }, {
 *             "product_category_id": 4,
 *             "quantity": 1,
 *             "unit_price": 2500.00
 *         }},
 *
 *         @OA\Items(ref="#/components/schemas/CreateOrderItem")
 *     ),
 *
 *     @OA\Property(property="delivery_fee", type="number", format="float", example=500.00, description="Frais de livraison"),
 *     @OA\Property(property="total_amount", type="number", format="float", example=15800.00, description="Montant total de la commande (sous-total + frais de livraison)"),
 *     @OA\Property(property="comments", type="string", maxLength=500, nullable=true, example="Livrer avant 18h", description="Commentaires optionnels (max 500 caractères)")
 * )
 *
 * @OA\Schema(
 *     schema="CreateOrderResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiResponse"),
 *         @OA\Schema(
 *
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(
 *                     property="order",
 *                     ref="#/components/schemas/OrderDetailsData"
 *                 )
 *             )
 *         )
 *     }
 * )
 *
 * @OA\Post(
 *     path="/api/orders",
 *     summary="Créer une nouvelle commande",
 *     description="Crée une nouvelle commande. Utilisez ensuite l'endpoint POST /api/orders/{order}/payment pour initier le paiement.",
 *     operationId="api.orders.store",
 *     tags={"Commandes"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\RequestBody(
 *         required=true,
 *
 *         @OA\JsonContent(ref="#/components/schemas/CreateOrderRequest")
 *     ),
 *
 *     @OA\Response(
 *         response=201,
 *         description="Commande créée avec succès",
 *
 *         @OA\JsonContent(ref="#/components/schemas/CreateOrderResponse")
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
 *         description="Accès refusé - rôle client requis",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=422,
 *         description="Erreur de validation",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
 *     )
 * )
 */
class StoreOrderControllerDoc {}
