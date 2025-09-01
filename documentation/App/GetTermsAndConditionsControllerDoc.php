<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="TermsAndConditionsData",
 *     @OA\Property(
 *         property="html_content",
 *         type="string",
 *         description="Contenu HTML structuré des conditions d'utilisation",
 *         example="<h1>Conditions d'utilisation</h1><p>Dernière mise à jour : 2025-09-01</p>..."
 *     ),
 *     @OA\Property(
 *         property="last_updated",
 *         type="string",
 *         description="Date de dernière mise à jour",
 *         example="2025-09-01"
 *     ),
 *     @OA\Property(
 *         property="sections",
 *         type="integer",
 *         description="Nombre de sections dans les conditions",
 *         example=2
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="TermsAndConditionsResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiResponse"),
 *         @OA\Schema(
 *             @OA\Property(
 *                 property="data",
 *                 ref="#/components/schemas/TermsAndConditionsData"
 *             ),
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Conditions d'utilisation récupérées avec succès."
 *             )
 *         )
 *     }
 * )
 *
 * @OA\Get(
 *     path="/api/app/terms-and-conditions",
 *     summary="Récupérer les conditions d'utilisation",
 *     description="Retourne les conditions d'utilisation de l'application au format HTML structuré",
 *     operationId="api.app.terms-and-conditions",
 *     tags={"App"},
 *     @OA\Response(
 *         response=200,
 *         description="Conditions d'utilisation récupérées avec succès",
 *         @OA\JsonContent(ref="#/components/schemas/TermsAndConditionsResponse")
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="_metadata",
 *                 type="object",
 *                 @OA\Property(property="success", type="boolean", example=false),
 *                 @OA\Property(property="message", type="string", example="Erreur lors de la récupération des conditions d'utilisation")
 *             )
 *         )
 *     )
 * )
 */
class GetTermsAndConditionsControllerDoc {}