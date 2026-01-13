<?php

namespace App\Http\Api\Controllers\App;

use App\Enums\AppType;
use App\Http\Api\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\AppVersion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

/**
 * @OA\Get(
 *     path="/api/app/version",
 *     summary="Obtenir les informations de version d'une application mobile",
 *     description="Récupère les informations de version pour Android et iOS d'une application spécifique (client ou livreur)",
 *     operationId="api.app.version",
 *     tags={"App"},
 *
 *     @OA\Parameter(
 *         name="app_type",
 *         in="query",
 *         required=true,
 *         description="Type d'application (customer_app ou delivery_app)",
 *
 *         @OA\Schema(
 *             type="string",
 *             enum={"customer_app", "delivery_app"},
 *             example="customer_app"
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Informations de version récupérées avec succès",
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(
 *                 property="_metadata",
 *                 type="object",
 *                 @OA\Property(property="success", type="boolean", example=true),
 *                 @OA\Property(property="message", type="string", example="Informations de version récupérées avec succès.")
 *             ),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(
 *                     property="android",
 *                     type="object",
 *                     nullable=true,
 *                     @OA\Property(property="version_code", type="integer", example=1),
 *                     @OA\Property(property="version_name", type="string", example="1.0.0"),
 *                     @OA\Property(property="update_required", type="boolean", example=false),
 *                     @OA\Property(property="release_notes", type="string", nullable=true, example="Version initiale de l'application"),
 *                     @OA\Property(property="app_link", type="string", nullable=true, example="https://play.google.com/store/apps/details?id=cm.petrolex.isogaz_delivery")
 *                 ),
 *                 @OA\Property(
 *                     property="ios",
 *                     type="object",
 *                     nullable=true,
 *                     @OA\Property(property="version_code", type="integer", example=1),
 *                     @OA\Property(property="version_name", type="string", example="1.0.0"),
 *                     @OA\Property(property="update_required", type="boolean", example=false),
 *                     @OA\Property(property="release_notes", type="string", nullable=true, example="Version initiale de l'application"),
 *                     @OA\Property(property="app_link", type="string", nullable=true, example="https://apps.apple.com/app/cm.petrolex.isogaz_delivery")
 *                 )
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=404,
 *         description="Aucune version disponible pour cette application",
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(
 *                 property="_metadata",
 *                 type="object",
 *                 @OA\Property(property="success", type="boolean", example=false),
 *                 @OA\Property(property="message", type="string", example="Aucune version disponible pour cette application.")
 *             ),
 *             @OA\Property(property="data", type="null", example=null)
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=422,
 *         description="Erreur de validation",
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(property="message", type="string", example="Le champ app type est obligatoire."),
 *             @OA\Property(
 *                 property="errors",
 *                 type="object",
 *                 @OA\Property(
 *                     property="app_type",
 *                     type="array",
 *
 *                     @OA\Items(type="string", example="Le champ app type est obligatoire.")
 *                 )
 *             )
 *         )
 *     )
 * )
 */
class GetAppVersionController extends Controller
{
    /**
     * Get the latest version information for a specific app type.
     *
     * Route: GET /app/version
     * Name: api.app.version
     */
    public function __invoke(Request $request): ApiResponse
    {
        $validated = $request->validate([
            'app_type' => ['required', 'string', Rule::in(AppType::values())],
        ]);

        $versions = AppVersion::getVersionsForApp($validated['app_type']);

        // Check if at least one platform has a version
        if ($versions['android'] === null && $versions['ios'] === null) {
            return ApiResponse::error(
                message: 'Aucune version disponible pour cette application.',
                statusCode: 404
            );
        }

        return ApiResponse::success(
            data: $versions,
            message: 'Informations de version récupérées avec succès.'
        );
    }
}
