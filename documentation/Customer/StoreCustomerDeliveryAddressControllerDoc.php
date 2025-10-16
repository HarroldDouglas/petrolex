<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="StoreCustomerDeliveryAddressRequest",
 *     required={
 *         "label",
 *         "address",
 *         "neighborhood_id"
 *     },
 *
 *     @OA\Property(property="label", type="string", example="Maison secondaire", description="Label for the delivery address"),
 *     @OA\Property(property="address", type="string", example="456 Avenue de la Liberté", description="Full street address"),
 *     @OA\Property(property="neighborhood_id", type="integer", example=1, description="ID of the neighborhood"),
 *     @OA\Property(property="latitude", type="number", format="float", example=3.848, nullable=true, description="Latitude coordinate"),
 *     @OA\Property(property="longitude", type="number", format="float", example=11.502, nullable=true, description="Longitude coordinate"),
 *     @OA\Property(property="phone", type="string", example="699887766", nullable=true, description="Contact phone number without country code"),
 *     @OA\Property(property="phone_country_code", type="string", example="+237", nullable=true, description="Country code for the phone number"),
 *     @OA\Property(property="contact_firstname", type="string", example="Marie", nullable=true, description="Contact person's first name"),
 *     @OA\Property(property="contact_lastname", type="string", example="Curie", nullable=true, description="Contact person's last name"),
 *     @OA\Property(property="email", type="string", format="email", example="test@example.com", nullable=true, description="Contact person's email address"),
 *     @OA\Property(property="address_precision", type="string", example="Bâtiment C, 3ème étage", nullable=true, description="Additional details for address precision"),
 *     @OA\Property(property="is_default", type="boolean", example=false, nullable=true, description="Whether this is the default delivery address for the customer"),
 * )
 *
 * @OA\Schema(
 *     schema="StoreCustomerDeliveryAddressResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiResponse"),
 *         @OA\Schema(
 *
 *             @OA\Property(
 *                 property="data",
 *                 ref="#/components/schemas/DeliveryAddress"
 *             ),
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Adresse de livraison créée avec succès"
 *             )
 *         )
 *     }
 * )
 *
 * @OA\Post(
 *     path="/api/my/delivery-addresses",
 *     summary="Créer une nouvelle adresse de livraison pour l'utilisateur connecté",
 *     description="Permet de créer une nouvelle adresse de livraison pour l'utilisateur connecté.",
 *     operationId="api.my.delivery-addresses.store",
 *     tags={"Livraison"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\RequestBody(
 *         required=true,
 *         description="Données de l'adresse de livraison à créer",
 *
 *         @OA\JsonContent(ref="#/components/schemas/StoreCustomerDeliveryAddressRequest")
 *     ),
 *
 *     @OA\Response(
 *         response=201,
 *         description="Adresse de livraison créée avec succès",
 *
 *         @OA\JsonContent(ref="#/components/schemas/StoreCustomerDeliveryAddressResponse")
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
 *         description="Client non trouvé",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=422,
 *         description="Erreur de validation",
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
class StoreCustomerDeliveryAddressControllerDoc {}
