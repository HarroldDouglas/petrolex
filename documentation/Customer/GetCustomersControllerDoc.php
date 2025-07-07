<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="CustomerData",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=5),
 *     @OA\Property(property="first_name", type="string", example="Jean"),
 *     @OA\Property(property="last_name", type="string", example="Dupont"),
 *     @OA\Property(property="email", type="string", example="jean.dupont@example.com"),
 *     @OA\Property(property="phone", type="string", example="+237612345678"),
 *     @OA\Property(property="address", type="string", example="123 Rue Principale, Douala"),
 *     @OA\Property(property="city", type="string", example="Douala"),
 *     @OA\Property(property="country", type="string", example="Cameroun"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="current_balance", type="number", format="float", example=15000.50),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-07-04T14:47:11.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-07-04T14:47:11.000000Z"),
 * )
 *
 * @OA\Schema(
 *     schema="CustomersResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiResponse"),
 *         @OA\Schema(
 *
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *
 *                 @OA\Items(ref="#/components/schemas/CustomerData")
 *             ),
 *
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Liste des clients récupérée avec succès"
 *             )
 *         )
 *     }
 * )
 *
 * @OA\Get(
 *     path="/api/customers",
 *     summary="Récupérer tous les clients",
 *     description="Récupérer la liste de tous les clients.",
 *     operationId="api.customers.index",
 *     tags={"Clients"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Response(
 *         response=200,
 *         description="Liste récupérée avec succès",
 *
 *         @OA\JsonContent(ref="#/components/schemas/CustomersResponse")
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
 *         response=500,
 *         description="Erreur interne du serveur",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 */
class GetCustomersControllerDoc {}
