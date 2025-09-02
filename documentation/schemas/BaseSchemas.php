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
 *     @OA\Property(property="email", type="string", format="email", example="jean.dupont@example.com"),
 *     @OA\Property(property="phone_number", type="string", example="+237677123456"),
 *     @OA\Property(property="address", type="string", nullable=true, example="123 Rue Principale, Douala"),
 *     @OA\Property(property="language", type="string", enum={"fr", "en"}, example="fr", description="User's preferred language"),
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
 *     description="Delivery address data",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="address_line_1", type="string", example="123 Rue Principale"),
 *     @OA\Property(property="address_line_2", type="string", nullable=true, example="Appartement 5"),
 *     @OA\Property(property="city", type="string", example="Douala"),
 *     @OA\Property(property="region", type="string", example="Littoral"),
 *     @OA\Property(property="postal_code", type="string", nullable=true, example="00237"),
 *     @OA\Property(property="country", type="string", example="Cameroun"),
 *     @OA\Property(property="latitude", type="number", format="float", nullable=true, example=4.0435),
 *     @OA\Property(property="longitude", type="number", format="float", nullable=true, example=9.7098),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T12:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T12:00:00Z")
 * )
 */
class BaseSchemas {}
