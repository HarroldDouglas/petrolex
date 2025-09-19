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
 *     @OA\Property(property="address", type="string", example="456 Avenue de la Liberté"),
 *     @OA\Property(
 *         property="neighborhood",
 *         ref="#/components/schemas/Neighborhood",
 *         nullable=true,
 *         description="Full neighborhood resource object"
 *     ),
 *     @OA\Property(
 *         property="municipality",
 *         ref="#/components/schemas/Municipality",
 *         nullable=true,
 *         description="Full municipality resource object"
 *     ),
 *     @OA\Property(
 *         property="city",
 *         ref="#/components/schemas/City",
 *         nullable=true,
 *         description="Full city resource object"
 *     ),
 *     @OA\Property(
 *         property="country",
 *         ref="#/components/schemas/Country",
 *         nullable=true,
 *         description="Full country resource object"
 *     ),
 *     @OA\Property(property="latitude", type="number", format="float", nullable=true, example=3.848),
 *     @OA\Property(property="longitude", type="number", format="float", nullable=true, example=11.502),
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
 */
class BaseSchemas {}
