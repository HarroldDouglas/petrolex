<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="CreateOrderItem",
 *     type="object",
 *     required={"product_category_id", "quantity", "unit_price"},
 *
 *     @OA\Property(property="product_category_id", type="integer", example=1, description="ID de la catégorie de produit (1=Bouteille 9Kg, 2=Brûleur, 3=Support fer, 4=Régulateur, 5=Tuyau 2m, 6=Tuyau 1.5m)"),
 *     @OA\Property(property="quantity", type="integer", minimum=1, maximum=100, example=2, description="Quantité commandée (1-100)"),
 *     @OA\Property(property="unit_price", type="number", format="float", example=6000.00, description="Prix unitaire du produit (Bouteille: content=6000, bottle_with_content=6500; Accessoires: Brûleur=2000, Support=4500, Régulateur=1500, Tuyau 2m=2000, Tuyau 1.5m=1500)"),
 *     @OA\Property(property="option", type="string", nullable=true, enum={"content", "bottle_with_content"}, example="content", description="Option du produit: content (recharge seulement 6000 XAF), bottle_with_content (bouteille pleine 6500 XAF), ou null (accessoires)")
 * )
 *
 * @OA\Schema(
 *     schema="CreateOrderRequest",
 *     type="object",
 *     required={"delivery_address_id", "distribution_center_id", "delivery_type", "items", "delivery_fee", "total_amount"},
 *
 *     @OA\Property(
 *         property="delivery_address_id",
 *         type="integer",
 *         example=1,
 *         description="ID de l'adresse de livraison du client. **Important**: l'adresse de livraison doit être dans la même municipalité que le centre de distribution sélectionné."
 *     ),
 *     @OA\Property(
 *         property="distribution_center_id",
 *         type="integer",
 *         example=1,
 *         description="ID du centre de distribution. **Important**: le centre de distribution doit être dans la même municipalité que l'adresse de livraison. Le centre ID 1 (Centre Yaoundé I) dessert la municipalité Yaoundé I."
 *     ),
 *     @OA\Property(property="delivery_type", type="string", enum={"normal", "fast"}, example="normal", description="Type de livraison: normal (standard) ou fast (express)"),
 *     @OA\Property(
 *         property="items",
 *         type="array",
 *         description="Liste des articles de la commande. Prix réels: Bouteille content=6000, bottle_with_content=6500; Brûleur=2000, Support=4500, Régulateur=1500, Tuyau 2m=2000, Tuyau 1.5m=1500",
 *         minItems=1,
 *         example={{
 *             "product_category_id": 1,
 *             "quantity": 2,
 *             "unit_price": 6000.00,
 *             "option": "content"
 *         }, {
 *             "product_category_id": 1,
 *             "quantity": 1,
 *             "unit_price": 6500.00,
 *             "option": "bottle_with_content"
 *         }, {
 *             "product_category_id": 4,
 *             "quantity": 1,
 *             "unit_price": 1500.00
 *         }},
 *
 *         @OA\Items(ref="#/components/schemas/CreateOrderItem")
 *     ),
 *
 *     @OA\Property(property="delivery_fee", type="number", format="float", example=500.00, description="Frais de livraison"),
 *     @OA\Property(property="total_amount", type="number", format="float", example=20500.00, description="Montant total de la commande (sous-total + frais de livraison). Calcul: (6000×2) + (6500×1) + (1500×1) + 500 = 20500"),
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
 *                 ),
 *                 @OA\Property(
 *                     property="wallet_info",
 *                     type="object",
 *                     description="Informations sur l'utilisation automatique du wallet lors de la création de la commande",
 *                     @OA\Property(property="wallet_balance_before", type="number", format="float", example=10000, description="Solde du wallet avant la commande (en FCFA)"),
 *                     @OA\Property(property="wallet_amount_used", type="number", format="float", example=7000, description="Montant automatiquement déduit du wallet (en FCFA)"),
 *                     @OA\Property(property="wallet_balance_after", type="number", format="float", example=3000, description="Solde du wallet après déduction (en FCFA)"),
 *                     @OA\Property(property="wallet_transaction_reference", type="string", nullable=true, example="WT_695D4FE8B8BE5_20260106190944", description="Référence de la transaction wallet (null si wallet non utilisé)")
 *                 ),
 *                 @OA\Property(
 *                     property="payment_info",
 *                     type="object",
 *                     description="Informations de paiement après utilisation du wallet",
 *                     @OA\Property(property="total_amount", type="number", format="float", example=7000, description="Montant total ORIGINAL de la commande (ne change jamais, en FCFA)"),
 *                     @OA\Property(property="total_amount_to_pay", type="number", format="float", example=0, description="Montant RESTANT à payer après déduction du wallet (en FCFA). Si 0, la commande est automatiquement marquée comme 'paid'"),
 *                     @OA\Property(property="payment_required", type="boolean", example=false, description="true = paiement externe requis (appeler POST /api/orders/{order}/payment), false = wallet a tout couvert, commande déjà payée")
 *                 )
 *             )
 *         )
 *     }
 * )
 *
 * @OA\Post(
 *     path="/api/orders",
 *     summary="Créer une nouvelle commande avec utilisation automatique du wallet",
 *     description="Crée une nouvelle commande et utilise AUTOMATIQUEMENT le solde du wallet du client si disponible. **Comportement**: 1) Si wallet couvre tout → commande marquée 'paid', payment_required=false. 2) Si wallet couvre partiellement → wallet déduit, payment_required=true, appeler POST /api/orders/{order}/payment pour le reste. 3) Si wallet vide → payment_required=true, appeler POST /api/orders/{order}/payment pour le montant total.",
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
 *         @OA\JsonContent(
 *             allOf={
 *
 *                 @OA\Schema(ref="#/components/schemas/ValidationErrorResponse"),
 *                 @OA\Schema(
 *
 *                     @OA\Property(
 *                         property="errors",
 *                         type="object",
 *                         description="Erreurs de validation possibles",
 *                         @OA\Property(
 *                             property="distribution_center_id",
 *                             type="array",
 *                             description="Erreur de cohérence géographique: l'adresse de livraison et le centre de distribution doivent être dans la même municipalité",
 *
 *                             @OA\Items(
 *                                 type="string",
 *                                 example="L'adresse de livraison (municipalité Yaoundé II) doit être dans la même municipalité que le centre de distribution (municipalité Yaoundé I)."
 *                             )
 *                         ),
 *
 *                         @OA\Property(
 *                             property="items.0.unit_price",
 *                             type="array",
 *                             description="Prix incorrect",
 *
 *                             @OA\Items(
 *                                 type="string",
 *                                 example="Prix incorrect: attendu 3900, fourni 4000"
 *                             )
 *                         ),
 *
 *                         @OA\Property(
 *                             property="delivery_fee",
 *                             type="array",
 *                             description="Frais de livraison incorrects",
 *
 *                             @OA\Items(
 *                                 type="string",
 *                                 example="Frais de livraison incorrect: attendu 500, fourni 600"
 *                             )
 *                         ),
 *
 *                         @OA\Property(
 *                             property="total_amount",
 *                             type="array",
 *                             description="Montant total incorrect",
 *
 *                             @OA\Items(
 *                                 type="string",
 *                                 example="Montant total incorrect: attendu 15800, fourni 16000"
 *                             )
 *                         )
 *                     )
 *                 )
 *             }
 *         )
 *     )
 * )
 */
class StoreOrderControllerDoc {}
