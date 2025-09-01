<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="TermsAndConditionsData",
 *
 *     @OA\Property(
 *         property="html_content",
 *         type="string",
 *         description="Contenu HTML structuré des conditions d'utilisation",
 *         example="<h1 style=""font-weight: bold; font-size: 18px; margin-bottom: 12px;"">Conditions d'utilisation</h1><p style=""margin-bottom: 16px; color: #666; font-size: 14px;"">Dernière mise à jour : 2025-09-01</p><p style=""margin-bottom: 20px; line-height: 1.6;"">Bienvenue dans l'application Petrolex...</p>"
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="TermsAndConditionsResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiResponse"),
 *         @OA\Schema(
 *
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
 *
 *     @OA\Response(
 *         response=200,
 *         description="Conditions d'utilisation récupérées avec succès",
 *
 *         @OA\JsonContent(ref="#/components/schemas/TermsAndConditionsResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur",
 *
 *         @OA\JsonContent(
 *
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
