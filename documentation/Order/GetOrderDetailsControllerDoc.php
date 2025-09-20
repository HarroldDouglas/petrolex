<?php

namespace App\Http\Api\Controllers;

use OpenApi\Annotations as OA;

/**
 * @OA\Get(
 *     path="/api/orders/{order}",
 *     operationId="getOrderDetails",
 *     summary="Récupérer les détails complets d'une commande",
 *     description="Retourne tous les détails d'une commande spécifique: informations de base, client, adresse de livraison, livreur, articles, paiement, suivi, etc.",
 *     tags={"Commandes"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="order",
 *         in="path",
 *         required=true,
 *         description="ID de la commande",
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Détails de la commande récupérés avec succès",
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(
 *                 property="_metadata",
 *                 type="object",
 *                 @OA\Property(property="success", type="boolean", example=true),
 *                 @OA\Property(property="message", type="string", example="Détails de la commande récupérés avec succès")
 *             ),
 *             @OA\Property(
 *                 property="data",
 *                 ref="#/components/schemas/OrderDetailsData"
 *             )
 *         )
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
 *         description="Non autorisé à voir cette commande",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=404,
 *         description="Commande non trouvée",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 */
class GetOrderDetailsControllerDoc {}

/**
 * @OA\Schema(
 *     schema="OrderDetailsData",
 *     title="OrderDetailsData",
 *     description="Détails complets d'une commande",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="order_number", type="string", example="CMD-202412-0001"),
 *     @OA\Property(property="status", type="string", example="confirmed"),
 *     @OA\Property(property="status_label", type="string", example="Confirmée"),
 *     @OA\Property(property="delivery_type", type="string", example="home_delivery"),
 *     @OA\Property(property="delivery_type_label", type="string", example="Livraison à domicile"),
 *     @OA\Property(property="subtotal", type="number", format="float", example=1500.00),
 *     @OA\Property(property="delivery_fee", type="number", format="float", example=500.00),
 *     @OA\Property(property="total_amount", type="number", format="float", example=2000.00),
 *     @OA\Property(property="total_refunded_amount", type="number", format="float", example=0.00),
 *     @OA\Property(property="order_date", type="string", format="date-time", example="2024-12-01T10:00:00.000000Z"),
 *     @OA\Property(property="delivery_date", type="string", format="date-time", nullable=true, example="2024-12-01T15:00:00.000000Z"),
 *     @OA\Property(property="confirmed_at", type="string", format="date-time", nullable=true, example="2024-12-01T10:05:00.000000Z"),
 *     @OA\Property(property="processing_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="delivered_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="cancelled_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-12-01T10:00:00.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-12-01T10:05:00.000000Z"),
 *     @OA\Property(property="comments", type="string", nullable=true, example="Livrer avant 18h"),
 *     @OA\Property(property="center_comments", type="string", nullable=true),
 *     @OA\Property(property="rating", type="integer", nullable=true, minimum=1, maximum=5),
 *     @OA\Property(property="cancelled_by", type="string", nullable=true),
 *     @OA\Property(property="cancelled_reason", type="string", nullable=true),
 *     @OA\Property(
 *         property="customer",
 *         type="object",
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="user_id", type="integer", example=1),
 *         @OA\Property(property="first_name", type="string", example="Jean"),
 *         @OA\Property(property="last_name", type="string", example="Dupont"),
 *         @OA\Property(property="full_name", type="string", example="Jean Dupont"),
 *         @OA\Property(property="email", type="string", example="jean.dupont@example.com"),
 *         @OA\Property(property="phone_number", type="string", example="677123456"),
 *         @OA\Property(property="current_balance", type="number", format="float", example=1500.00),
 *         @OA\Property(property="country", ref="#/components/schemas/Country")
 *     ),
 *     @OA\Property(property="delivery_address", ref="#/components/schemas/DeliveryAddress"),
 *     @OA\Property(
 *         property="delivery_person",
 *         type="object",
 *         nullable=true,
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="user_id", type="integer", example=5),
 *         @OA\Property(property="first_name", type="string", example="Paul"),
 *         @OA\Property(property="last_name", type="string", example="Martin"),
 *         @OA\Property(property="full_name", type="string", example="Paul Martin"),
 *         @OA\Property(property="phone_number", type="string", example="677987654"),
 *         @OA\Property(property="email", type="string", example="paul.martin@example.com")
 *     ),
 *     @OA\Property(
 *         property="distribution_center",
 *         type="object",
 *         nullable=true,
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="Centre Yaoundé"),
 *         @OA\Property(property="address", type="string", example="123 Rue de la Paix"),
 *         @OA\Property(property="phone", type="string", example="677111222"),
 *         @OA\Property(property="latitude", type="number", format="float", example=3.848),
 *         @OA\Property(property="longitude", type="number", format="float", example=11.502)
 *     ),
 *     @OA\Property(
 *         property="payment",
 *         type="object",
 *         nullable=true,
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="payment_method", type="string", example="orange_money"),
 *         @OA\Property(property="payment_method_label", type="string", example="Orange Money"),
 *         @OA\Property(property="payment_status", type="string", example="paid"),
 *         @OA\Property(property="payment_status_label", type="string", example="Payé"),
 *         @OA\Property(property="amount", type="number", format="float", example=2000.00),
 *         @OA\Property(property="payment_reference", type="string", example="PAY_123456"),
 *         @OA\Property(property="payment_url", type="string", nullable=true),
 *         @OA\Property(property="gateway_transaction_id", type="string", nullable=true),
 *         @OA\Property(property="gateway_response", type="object", nullable=true),
 *         @OA\Property(property="paid_at", type="string", format="date-time", nullable=true),
 *         @OA\Property(property="failed_at", type="string", format="date-time", nullable=true),
 *         @OA\Property(property="created_at", type="string", format="date-time")
 *     ),
 *     @OA\Property(
 *         property="items",
 *         type="array",
 *
 *         @OA\Items(ref="#/components/schemas/OrderItemDetails")
 *     ),
 *
 *     @OA\Property(
 *         property="bottle_movements",
 *         type="array",
 *
 *         @OA\Items(ref="#/components/schemas/BottleMovement")
 *     ),
 *
 *     @OA\Property(
 *         property="refunds",
 *         type="array",
 *
 *         @OA\Items(ref="#/components/schemas/RefundDetails")
 *     ),
 *
 *     @OA\Property(property="delivery_tracking", ref="#/components/schemas/DeliveryTracking", nullable=true),
 *     @OA\Property(
 *         property="destination_coordinates",
 *         type="object",
 *         @OA\Property(property="latitude", type="number", format="float", example=3.848),
 *         @OA\Property(property="longitude", type="number", format="float", example=11.502)
 *     ),
 *     @OA\Property(
 *         property="permissions",
 *         type="object",
 *         @OA\Property(property="can_be_rated", type="boolean", example=false),
 *         @OA\Property(property="can_be_cancelled", type="boolean", example=true),
 *         @OA\Property(property="can_be_delivered", type="boolean", example=true),
 *         @OA\Property(property="can_change_delivery_person", type="boolean", example=true),
 *         @OA\Property(property="can_scan_bottles", type="boolean", example=true),
 *         @OA\Property(property="can_be_tracked", type="boolean", example=true)
 *     ),
 *     @OA\Property(
 *         property="bottle_info",
 *         type="object",
 *         @OA\Property(property="has_bottle_items", type="boolean", example=true),
 *         @OA\Property(property="has_refunds", type="boolean", example=false),
 *         @OA\Property(property="all_bottles_scanned", type="boolean", example=false),
 *         @OA\Property(property="bottle_scan_progress", type="integer", example=75, description="Pourcentage de bouteilles scannées")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="OrderItemDetails",
 *     title="OrderItemDetails",
 *     description="Détails d'un article dans une commande",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="quantity", type="integer", example=2),
 *     @OA\Property(property="unit_price", type="number", format="float", example=750.00),
 *     @OA\Property(property="total_price", type="number", format="float", example=1500.00),
 *     @OA\Property(property="scanned_bottles_count", type="integer", example=1),
 *     @OA\Property(
 *         property="product",
 *         type="object",
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="Bouteille 19L"),
 *         @OA\Property(property="description", type="string", example="Bouteille d'eau potable 19 litres"),
 *         @OA\Property(property="sku", type="string", example="BTL-19L-001"),
 *         @OA\Property(property="barcode", type="string", example="1234567890"),
 *         @OA\Property(property="image_url", type="string", nullable=true),
 *         @OA\Property(property="is_active", type="boolean", example=true),
 *         @OA\Property(
 *             property="category",
 *             type="object",
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="name", type="string", example="Bouteilles"),
 *             @OA\Property(property="product_type", type="string", example="bottle"),
 *             @OA\Property(property="product_type_label", type="string", example="Bouteille")
 *         ),
 *         @OA\Property(
 *             property="bottle",
 *             type="object",
 *             nullable=true,
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="capacity", type="number", format="float", example=19.0),
 *             @OA\Property(property="capacity_unit", type="string", example="L"),
 *             @OA\Property(property="deposit_amount", type="number", format="float", example=2000.00),
 *             @OA\Property(
 *                 property="bottle_type",
 *                 type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="name", type="string", example="Standard 19L")
 *             )
 *         ),
 *         @OA\Property(
 *             property="accessory",
 *             type="object",
 *             nullable=true,
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="warranty_period", type="integer", example=12),
 *             @OA\Property(property="warranty_unit", type="string", example="months"),
 *             @OA\Property(
 *                 property="accessory_type",
 *                 type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="name", type="string", example="Distributeur")
 *             )
 *         )
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="BottleMovement",
 *     title="BottleMovement",
 *     description="Mouvement de consigne de bouteille",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="bottle_id", type="integer", example=1),
 *     @OA\Property(property="movement_type", type="string", example="deposit"),
 *     @OA\Property(property="movement_type_label", type="string", example="Consigne"),
 *     @OA\Property(property="quantity", type="integer", example=2),
 *     @OA\Property(property="unit_deposit_amount", type="number", format="float", example=2000.00),
 *     @OA\Property(property="total_deposit_amount", type="number", format="float", example=4000.00),
 *     @OA\Property(property="created_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="RefundDetails",
 *     title="RefundDetails",
 *     description="Détails d'un remboursement",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="amount", type="number", format="float", example=500.00),
 *     @OA\Property(property="reason", type="string", example="Produit défectueux"),
 *     @OA\Property(property="status", type="string", example="processed"),
 *     @OA\Property(property="status_label", type="string", example="Traité"),
 *     @OA\Property(property="processed_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="DeliveryTracking",
 *     title="DeliveryTracking",
 *     description="Suivi de livraison en temps réel",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="status", type="string", example="in_progress"),
 *     @OA\Property(property="status_label", type="string", example="En cours"),
 *     @OA\Property(property="current_latitude", type="number", format="float", nullable=true, example=3.850),
 *     @OA\Property(property="current_longitude", type="number", format="float", nullable=true, example=11.500),
 *     @OA\Property(property="estimated_arrival", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="started_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="completed_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
