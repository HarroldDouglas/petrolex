<?php

use OpenApi\Annotations as OA;

// Schémas déplacés vers documentation/schemas/shared/

/**
 * @OA\Schema(
 *     schema="CustomerData",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/UserData"),
 *         @OA\Schema(
 *
 *             @OA\Property(
 *                 property="customer_id",
 *                 type="integer",
 *                 example=1,
 *                 description="Unique identifier for the customer record"
 *             ),
 *             @OA\Property(
 *                 property="deliveryAddresses",
 *                 type="array",
 *
 *                 @OA\Items(ref="#/components/schemas/DeliveryAddress")
 *             ),
 *
 *             @OA\Property(
 *                 property="current_balance",
 *                 type="number",
 *                 format="float",
 *                 example=15000.50,
 *                 nullable=true
 *             )
 *         )
 *     }
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
 */

/*
// Documentation supprimée - endpoint réservé aux tests internes uniquement
// @OA\Get(
//     path="/api/customers",
//     summary="Récupérer tous les clients",
//     description="Récupérer la liste de tous les clients avec leurs informations utilisateur et adresses de livraison.",
//     operationId="api.customers.index",
//     tags={"Clients"},
//     security={{"bearerAuth":{}}},
//
//     @OA\Response(
//         response=200,
//         description="Liste récupérée avec succès",
//
//         @OA\JsonContent(ref="#/components/schemas/CustomersResponse")
//     ),
//
//     @OA\Response(
//         response=401,
//         description="Non autorisé",
//
//         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
//     ),
//
//     @OA\Response(
//         response=500,
//         description="Erreur interne du serveur",
//
//         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
//     )
// )
*/
class GetCustomersControllerDoc {}
