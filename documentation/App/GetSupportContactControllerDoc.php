<?php

namespace Documentation\App;

use OpenApi\Annotations as OA;

/**
 * @OA\Get(
 *     path="/api/app/support/contact",
 *     summary="Obtenir les informations de contact du support",
 *     description="Retourne les informations de contact (numéro de téléphone et email) de l'équipe de support",
 *     operationId="getSupportContact",
 *     tags={"App"},
 *
 *     @OA\Response(
 *         response=200,
 *         description="Informations de contact du support récupérées avec succès",
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(
 *                 property="_metadata",
 *                 type="object",
 *                 @OA\Property(property="success", type="boolean", example=true),
 *                 @OA\Property(property="message", type="string", example="Informations de contact du support récupérées avec succès.")
 *             ),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="phone_number", type="string", example="+237 6 77 88 99 00"),
 *                 @OA\Property(property="email", type="string", format="email", example="support@petrolex.cm")
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur interne",
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(
 *                 property="_metadata",
 *                 type="object",
 *                 @OA\Property(property="success", type="boolean", example=false),
 *                 @OA\Property(property="message", type="string", example="Une erreur interne s'est produite.")
 *             )
 *         )
 *     )
 * )
 */
class GetSupportContactControllerDoc
{
    // This class is only for OpenAPI documentation
}
