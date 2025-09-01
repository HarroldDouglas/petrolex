<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="PrivacyPolicyData",
 *     @OA\Property(
 *         property="html_content",
 *         type="string",
 *         description="Contenu HTML structuré de la politique de confidentialité",
 *         example="<h1 style=""font-weight: bold; font-size: 18px; margin-bottom: 12px;"">Politique de confidentialité</h1><p style=""margin-bottom: 16px; color: #666; font-size: 14px;"">Dernière mise à jour : 2025-09-01</p><p style=""margin-bottom: 20px; line-height: 1.6;"">Votre vie privée est importante pour nous...</p>"
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="PrivacyPolicyResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiResponse"),
 *         @OA\Schema(
 *             @OA\Property(
 *                 property="data",
 *                 ref="#/components/schemas/PrivacyPolicyData"
 *             ),
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Politique de confidentialité récupérée avec succès."
 *             )
 *         )
 *     }
 * )
 *
 * @OA\Get(
 *     path="/api/app/privacy-policy",
 *     summary="Récupérer la politique de confidentialité",
 *     description="Retourne la politique de confidentialité de l'application au format HTML structuré",
 *     operationId="api.app.privacy-policy",
 *     tags={"App"},
 *     @OA\Response(
 *         response=200,
 *         description="Politique de confidentialité récupérée avec succès",
 *         @OA\JsonContent(ref="#/components/schemas/PrivacyPolicyResponse")
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="_metadata",
 *                 type="object",
 *                 @OA\Property(property="success", type="boolean", example=false),
 *                 @OA\Property(property="message", type="string", example="Erreur lors de la récupération de la politique de confidentialité")
 *             )
 *         )
 *     )
 * )
 */
class GetPrivacyPolicyControllerDoc {}
