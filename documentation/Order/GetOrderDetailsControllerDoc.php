<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Get(
 *     path="/api/orders/{order}",
 *     operationId="api.orders.show",
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
