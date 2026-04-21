<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="Language",
 *     type="string",
 *     enum={"fr", "en"},
 *     description="Supported languages",
 *     example="fr"
 * )
 * @OA\Schema(
 *     schema="ApiResponse",
 *     description="Réponse standard de l'API",
 *
 *     @OA\Property(
 *         property="_metadata",
 *         type="object",
 *         @OA\Property(property="success", type="boolean", example=true),
 *         @OA\Property(property="message", type="string", example="Opération réussie")
 *     ),
 *     @OA\Property(property="data", type="object", nullable=true, description="Données retournées par l'API")
 * )
 *
 * @OA\Schema(
 *      schema="ErrorResponse",
 *
 *      @OA\Property(
 *          property="_metadata",
 *          type="object",
 *          @OA\Property(property="success", type="boolean", example=false),
 *          @OA\Property(property="message", type="string", example="Une erreur est survenue.")
 *      ),
 *      @OA\Property(property="data", type="object", nullable=true, example=null)
 *  )
 *
 * @OA\Schema(
 *     schema="ValidationErrorDetail",
 *     type="array",
 *
 *     @OA\Items(type="string", example="Ce champ est requis.")
 * )
 *
 * @OA\Schema(
 *     schema="ValidationErrorResponse",
 *
 *     @OA\Property(
 *         property="_metadata",
 *         type="object",
 *         @OA\Property(property="success", type="boolean", example=false),
 *         @OA\Property(property="message", type="string", example="Erreur de validation.")
 *     ),
 *     @OA\Property(
 *         property="data",
 *         type="object",
 *         description="Détail des erreurs de validation",
 *         properties={
 *             @OA\Property(property="field_name", ref="#/components/schemas/ValidationErrorDetail")
 *         }
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="UserData",
 *     title="UserData",
 *     description="User data for API responses",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="first_name", type="string", example="Jean"),
 *     @OA\Property(property="last_name", type="string", example="Dupont"),
 *     @OA\Property(property="full_name", type="string", example="Jean Dupont"),
 *     @OA\Property(property="email", type="string", format="email", example="test@example.com"),
 *     @OA\Property(property="phone_number", type="string", nullable=true, example="677123456", description="Numéro de téléphone sans code pays"),
 *     @OA\Property(property="address", type="string", nullable=true, example="123 Rue Principale, Douala"),
 *     @OA\Property(property="language", type="string", enum={"fr", "en"}, example="fr", description="User's preferred language"),
 *     @OA\Property(property="country", ref="#/components/schemas/Country", nullable=true, description="User's country information including phone code, currency, and decimal places for formatting"),
 *     @OA\Property(
 *           property="delivery_addresses",
 *           type="array",
 *
 *           @OA\Items(ref="#/components/schemas/DeliveryAddress"),
 *           nullable=true,
 *           description="User's delivery addresses (array of delivery address objects, only for customers)"
 *       ),
 *
 *     @OA\Property(property="current_balance", type="number", format="float", nullable=true, example=1500.00, description="Customer current balance (only for customers)"),
 *     @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true, example="2024-01-01T12:00:00.000000Z"),
 *     @OA\Property(property="phone_verified_at", type="string", format="date-time", nullable=true, example="2024-01-01T12:00:00.000000Z"),
 *     @OA\Property(property="last_login_at", type="string", format="date-time", nullable=true, example="2024-12-01T08:45:00.000000Z"),
 *     @OA\Property(property="roles", type="array", @OA\Items(type="string"), example={"customer"}),
 *     @OA\Property(property="customer_id", type="integer", nullable=true, example=123, description="Customer ID if user is a customer"),
 *     @OA\Property(property="delivery_person_id", type="integer", nullable=true, example=456, description="Delivery person ID if user is a delivery person"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T12:00:00.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-12-01T08:45:00.000000Z")
 * )
 *
 * @OA\Schema(
 *     schema="DeliveryAddress",
 *     title="DeliveryAddress",
 *     description="Customer delivery address data",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="label", type="string", example="Maison"),
 *     @OA\Property(property="address", type="string", nullable=true, example="456 Avenue de la Liberté"),
 *     @OA\Property(
 *         property="neighborhood",
 *         type="object",
 *         nullable=true,
 *         description="Neighborhood data without nested relationships",
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="Bali"),
 *         @OA\Property(property="municipality_id", type="integer", example=1),
 *         @OA\Property(property="is_active", type="boolean", example=true),
 *         @OA\Property(property="latitude", type="string", example="3.85600000"),
 *         @OA\Property(property="longitude", type="string", example="11.49500000")
 *     ),
 *     @OA\Property(
 *         property="municipality",
 *         type="object",
 *         nullable=true,
 *         description="Municipality data without nested relationships",
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="Yaoundé I"),
 *         @OA\Property(property="city_id", type="integer", example=1)
 *     ),
 *     @OA\Property(
 *         property="city",
 *         type="object",
 *         nullable=true,
 *         description="City data without nested relationships",
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="Yaoundé"),
 *         @OA\Property(property="country_id", type="integer", example=1)
 *     ),
 *     @OA\Property(
 *         property="country",
 *         type="object",
 *         nullable=true,
 *         description="Complete country data",
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="Cameroun"),
 *         @OA\Property(property="code", type="string", example="CM"),
 *         @OA\Property(property="phone_code", type="string", example="+237"),
 *         @OA\Property(property="currency", type="string", example="FCFA"),
 *         @OA\Property(property="decimal_places", type="integer", example=0),
 *         @OA\Property(property="is_active", type="boolean", example=true)
 *     ),
 *     @OA\Property(property="latitude", type="number", format="float", nullable=true, example=3.848),
 *     @OA\Property(property="longitude", type="number", format="float", nullable=true, example=11.502),
 *     @OA\Property(property="location_link", type="string", nullable=true, example="https://maps.google.com/?q=3.848,11.502", description="Geolocation link (alternative to GPS coordinates)"),
 *     @OA\Property(property="phone", type="string", nullable=true, example="699887766"),
 *     @OA\Property(property="phone_country_code", type="string", nullable=true, example="+237"),
 *     @OA\Property(property="contact_firstname", type="string", nullable=true, example="Marie"),
 *     @OA\Property(property="contact_lastname", type="string", nullable=true, example="Curie"),
 *     @OA\Property(property="contact_full_name", type="string", example="Marie Curie"),
 *     @OA\Property(property="email", type="string", format="email", nullable=true, example="test@example.com"),
 *     @OA\Property(property="address_precision", type="string", nullable=true, example="Bâtiment C, 3ème étage"),
 *     @OA\Property(property="is_default", type="boolean", example=false),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T12:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T12:00:00Z")
 * )
 *
 * @OA\Schema(
 *     schema="Country",
 *     title="Country",
 *     description="Country data with phone code and currency",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Cameroun"),
 *     @OA\Property(property="code", type="string", example="CM"),
 *     @OA\Property(property="phone_code", type="string", nullable=true, example="+237"),
 *     @OA\Property(property="currency", type="string", nullable=true, example="FCFA", description="Currency symbol/label from Currency enum (FCFA, $, €)"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="decimal_places", type="integer", example=0, description="Number of decimal places for currency formatting (0 for FCFA, 2 for $/€)")
 * )
 *
 * @OA\Schema(
 *     schema="City",
 *     title="City",
 *     description="City data",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Yaoundé"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="country", ref="#/components/schemas/Country", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="Municipality",
 *     title="Municipality",
 *     description="Municipality data",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Yaoundé I"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="city", ref="#/components/schemas/City", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="Neighborhood",
 *     title="Neighborhood",
 *     description="Neighborhood data",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Bali"),
 *     @OA\Property(property="latitude", type="number", format="float", nullable=true, example=3.848),
 *     @OA\Property(property="longitude", type="number", format="float", nullable=true, example=11.502),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="municipality", ref="#/components/schemas/Municipality", nullable=true),
 *     @OA\Property(property="city", ref="#/components/schemas/City", nullable=true)
 * )
/**
 * @OA\Schema(
 *     schema="OrderDetailsData",
 *     title="OrderDetailsData",
 *     description="Détails complets d'une commande - Structure uniforme pour tous les endpoints Order",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="order_number", type="string", example="CMD-202412-0001"),
 *     @OA\Property(property="status", type="string", example="confirmed"),
 *     @OA\Property(property="status_label", type="string", example="Confirmée"),
 *     @OA\Property(property="delivery_type", type="string", example="home_delivery"),
 *     @OA\Property(property="delivery_type_label", type="string", example="Livraison à domicile"),
 *     @OA\Property(property="subtotal", type="string", example="1500.00", description="Sous-total de la commande (format string)"),
 *     @OA\Property(property="delivery_fee", type="string", example="500.00", description="Frais de livraison (format string)"),
 *     @OA\Property(property="total_amount", type="string", example="2000.00", description="Montant total de la commande (format string)"),
 *     @OA\Property(property="wallet_amount_used", type="string", example="0.00", description="Montant payé par le wallet du client lors de la création de la commande (format string)"),
 *     @OA\Property(property="total_amount_to_pay", type="string", example="2000.00", description="Montant restant à payer après déduction du wallet (total_amount - wallet_amount_used) (format string)"),
 *     @OA\Property(property="total_refunded_amount", type="string", example="0.00", description="Montant total remboursé (format string)"),
 *     @OA\Property(property="order_date", type="string", format="date-time", example="2024-12-01T10:00:00.000000Z"),
 *     @OA\Property(property="delivery_date", type="string", format="date-time", nullable=true, example="2024-12-01T15:00:00.000000Z"),
 *     @OA\Property(property="paid_at", type="string", format="date-time", nullable=true, example="2024-12-01T10:05:00.000000Z"),
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
 *         nullable=true,
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
 *     @OA\Property(property="delivery_address", ref="#/components/schemas/DeliveryAddress", nullable=true),
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
 *         @OA\Property(property="amount_paid", type="number", format="float", example=2000.00),
 *         @OA\Property(property="amount_due", type="number", format="float", example=0.00),
 *         @OA\Property(property="payment_reference", type="string", example="PAY_123456"),
 *         @OA\Property(property="transaction_reference", type="string", nullable=true, example="TXN_987654"),
 *         @OA\Property(property="payment_url", type="string", nullable=true),
 *         @OA\Property(property="gateway_response", type="object", nullable=true),
 *         @OA\Property(property="payment_date", type="string", format="date-time", nullable=true, example="2024-12-01T10:05:00.000000Z"),
 *         @OA\Property(property="payment_notes", type="string", nullable=true),
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
 *         property="bottle_info",
 *         type="object",
 *         @OA\Property(property="has_bottle_items", type="boolean", example=true),
 *         @OA\Property(property="has_refunds", type="boolean", example=false),
 *         @OA\Property(property="all_bottles_scanned", type="boolean", example=false),
 *         @OA\Property(property="bottle_scan_progress", type="integer", example=75, description="Pourcentage de bouteilles scannées")
 *     ),
 *     @OA\Property(property="invoice_url", type="string", format="uri", example="http://127.0.0.1:8001/api/orders/1/download/invoice", description="URL de téléchargement de la facture PDF")
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
 *     @OA\Property(property="bottle_type", type="string", nullable=true, example="bottle_with_content"),
 *     @OA\Property(property="bottle_type_label", type="string", nullable=true, example="Nouvelle bouteille"),
 *     @OA\Property(
 *         property="product_category",
 *         type="object",
 *         nullable=true,
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="Bouteilles 19L"),
 *         @OA\Property(property="product_type", type="string", example="bottle"),
 *         @OA\Property(property="product_type_label", type="string", example="Bouteille")
 *     ),
 *     @OA\Property(property="image", type="object", nullable=true, description="Image principale du produit (ou null)",
 *         @OA\Property(property="url", type="string", example="https://example.com/image.jpg"),
 *         @OA\Property(property="thumb", type="string", example="https://example.com/image-thumb.jpg"),
 *         @OA\Property(property="medium", type="string", example="https://example.com/image-medium.jpg"),
 *         @OA\Property(property="large", type="string", example="https://example.com/image-large.jpg"),
 *         @OA\Property(property="is_default", type="boolean", example=true)
 *     ),
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
 *     @OA\Property(property="status", type="string", example="processing"),
 *     @OA\Property(property="status_label", type="string", example="En cours"),
 *     @OA\Property(property="current_latitude", type="number", format="float", nullable=true, example=3.850),
 *     @OA\Property(property="current_longitude", type="number", format="float", nullable=true, example=11.500),
 *     @OA\Property(property="estimated_arrival", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="destination_lat", type="number", format="float", nullable=true, example=3.848, description="Destination latitude from delivery address"),
 *     @OA\Property(property="destination_lng", type="number", format="float", nullable=true, example=11.502, description="Destination longitude from delivery address"),
 *     @OA\Property(property="destination_location_link", type="string", nullable=true, example="https://maps.google.com/?q=3.848,11.502", description="Destination geolocation link (alternative to GPS coordinates)"),
 *     @OA\Property(property="destination_address", type="string", nullable=true, example="Maison, Bali, Yaoundé", description="Full formatted destination address"),
 *     @OA\Property(property="started_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="completed_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="OrderPaymentData",
 *     title="OrderPaymentData",
 *     description="Données d'un paiement de commande",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="status", type="string", example="pending", description="Statut du paiement"),
 *     @OA\Property(property="date", type="string", format="date-time", nullable=true, description="Date du paiement"),
 *     @OA\Property(property="reference", type="string", example="PAY_2025_001234", description="Référence du paiement"),
 *     @OA\Property(property="method", type="string", example="orange_money", description="Méthode de paiement"),
 *     @OA\Property(property="method_label", type="string", example="Orange Money", description="Libellé de la méthode de paiement")
 * )
 */
class BaseSchemas {}
