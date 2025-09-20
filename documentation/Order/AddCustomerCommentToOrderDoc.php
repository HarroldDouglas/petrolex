<?php

namespace App\Documentation\Order;

use OpenApi\Annotations as OA;

/**
 * @OA\Post(
 *     path="/api/orders/{order}/customer-feedback",
 *     summary="Ajouter un commentaire et une note à une commande",
 *     description="Permet au client d'ajouter un commentaire et une note (de 1 à 5) à une commande après livraison.",
 *     operationId="api.orders.addCustomerComment",
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
 *     @OA\RequestBody(
 *         required=true,
 *         description="Données du commentaire et de la note",
 *
 *         @OA\JsonContent(
 *             required={"comments", "rating"},
 *
 *             @OA\Property(property="comments", type="string", minLength=10, description="Commentaire du client (minimum 10 caractères)", example="Service excellent, livraison rapide et produit de qualité !"),
 *             @OA\Property(property="rating", type="number", format="float", minimum=1, maximum=5, description="Note de 1 à 5", example=4.5)
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Commentaire et note ajoutés avec succès",
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(property="_metadata", type="object",
 *                 @OA\Property(property="success", type="boolean", example=true),
 *                 @OA\Property(property="message", type="string", example="Commentaire ajouté avec succès")
 *             ),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="order_id", type="integer", example=1),
 *                 @OA\Property(property="comments", type="string", example="Service excellent, livraison rapide et produit de qualité !"),
 *                 @OA\Property(property="rating", type="number", format="float", example=4.5),
 *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-09-20T16:30:00.000000Z")
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=401,
 *         description="Non authentifié",
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(property="message", type="string", example="Unauthenticated.")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=403,
 *         description="Accès refusé - La commande n'appartient pas au client",
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(property="message", type="string", example="Cette commande ne vous appartient pas.")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=404,
 *         description="Commande non trouvée",
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(property="message", type="string", example="Commande non trouvée.")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=422,
 *         description="Erreurs de validation",
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(property="message", type="string", example="The given data was invalid."),
 *             @OA\Property(property="errors", type="object",
 *                 @OA\Property(property="comments", type="array", @OA\Items(type="string", example="Le commentaire doit contenir au moins 10 caractères.")),
 *                 @OA\Property(property="rating", type="array", @OA\Items(type="string", example="La note doit être comprise entre 1 et 5."))
 *             )
 *         )
 *     )
 * )
 */
class AddCustomerCommentToOrderDoc {}
