<?php

namespace App\Documentation\Order;

use OpenApi\Annotations as OA;

/**
 * @OA\Get(
 *     path="/api/orders",
 *     summary="Lister toutes les commandes",
 *     description="Récupère une liste de toutes les commandes existantes, incluant leurs articles.",
 *     operationId="api.orders.index",
 *     tags={"Commandes"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Response(
 *         response=200,
 *         description="Liste des commandes récupérée avec succès.",
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(property="_metadata", type="object",
 *                 @OA\Property(property="success", type="boolean", example=true),
 *                 @OA\Property(property="message", type="string", example="Liste des commandes récupérée avec succès.")
 *             ),
 *             @OA\Property(property="data", type="array",
 *
 *                 @OA\Items(ref="#/components/schemas/OrderData")
 *             )
 *         )
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
class GetOrdersControllerDoc {}
