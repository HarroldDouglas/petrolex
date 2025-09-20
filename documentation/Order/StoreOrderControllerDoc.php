<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="CreateOrderItem",
 *     type="object",
 *     required={"product_category_id", "quantity"},
 *
 *     @OA\Property(property="product_category_id", type="integer", example=5, description="ID de la catégorie de produit"),
 *     @OA\Property(property="quantity", type="integer", minimum=1, maximum=100, example=2, description="Quantité commandée (1-100)"),
 *     @OA\Property(property="option", type="string", nullable=true, enum={"content", "bottle_with_content"}, example="bottle_with_content", description="Option du produit: content (recharge seulement), bottle_with_content (bouteille pleine), ou null (accessoires)")
 * )
 *
 * @OA\Schema(
 *     schema="CreateOrderRequest",
 *     type="object",
 *     required={"delivery_address_id", "distribution_center_id", "delivery_type", "payment_method", "items"},
 *
 *     @OA\Property(property="delivery_address_id", type="integer", example=7, description="ID de l'adresse de livraison du client"),
 *     @OA\Property(property="distribution_center_id", type="integer", example=3, description="ID du centre de distribution"),
 *     @OA\Property(property="delivery_type", type="string", enum={"normal", "fast"}, example="normal", description="Type de livraison"),
 *     @OA\Property(property="payment_method", type="string", enum={"orange_money", "mtn_money", "credit_card"}, example="orange_money", description="Méthode de paiement"),
 *     @OA\Property(
 *         property="items",
 *         type="array",
 *         description="Liste des articles de la commande (1 bouteille recharge, 1 bouteille pleine, 1 accessoire)",
 *         minItems=1,
 *
 *         @OA\Items(ref="#/components/schemas/CreateOrderItem")
 *     ),
 *
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
 *                     type="object",
 *                     @OA\Property(property="id", type="integer", example=123),
 *                     @OA\Property(property="order_number", type="string", example="ORD-2025-001234"),
 *                     @OA\Property(property="status", type="string", example="pending"),
 *                     @OA\Property(property="status_label", type="string", example="En attente"),
 *                     @OA\Property(property="delivery_type", type="string", example="normal"),
 *                     @OA\Property(property="delivery_type_label", type="string", example="Livraison normale"),
 *                     @OA\Property(property="subtotal", type="number", format="float", example=15000),
 *                     @OA\Property(property="delivery_fee", type="number", format="float", example=2000),
 *                     @OA\Property(property="total_amount", type="number", format="float", example=17000),
 *                     @OA\Property(property="comments", type="string", nullable=true, example="Livrer avant 18h"),
 *                     @OA\Property(property="customer", type="object", description="Informations du client"),
 *                     @OA\Property(property="delivery_address", type="object", description="Adresse de livraison"),
 *                     @OA\Property(property="items", type="array", @OA\Items(type="object"), description="Articles de la commande"),
 *                     @OA\Property(property="invoice_url", type="string", nullable=true, format="uri", example="https://app.petrolex.cm/api/orders/123/download/invoice", description="URL de téléchargement de la facture PDF (générée automatiquement après création)")
 *                 ),
 *                 @OA\Property(
 *                     property="payment",
 *                     type="object",
 *                     @OA\Property(property="payment_reference", type="string", example="PAY_2025_001234"),
 *                     @OA\Property(property="payment_status", type="string", example="pending"),
 *                     @OA\Property(property="payment_status_label", type="string", example="En attente"),
 *                     @OA\Property(property="payment_method", type="string", example="orange_money"),
 *                     @OA\Property(property="payment_method_label", type="string", example="Orange Money"),
 *                     @OA\Property(property="amount_due", type="number", format="float", example=17000),
 *                     @OA\Property(property="amount_paid", type="number", format="float", example=0)
 *                 )
 *             )
 *         )
 *     }
 * )
 *
 * @OA\Post(
 *     path="/api/orders",
 *     summary="Créer une nouvelle commande",
 *     description="Crée une nouvelle commande avec initiation du paiement. ENDPOINT PRINCIPAL pour passer des commandes.",
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
