<?php

namespace Documentation\App;

use OpenApi\Annotations as OA;

/**
 * @OA\Get(
 *     path="/api/app/support/contact",
 *     summary="Get support team contact information",
 *     description="Returns the contact information (phone number and email) for the support team",
 *     operationId="getSupportContact",
 *     tags={"App"},
 *
 *     @OA\Response(
 *         response=200,
 *         description="Support contact information retrieved successfully",
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
 *         description="Internal server error",
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
