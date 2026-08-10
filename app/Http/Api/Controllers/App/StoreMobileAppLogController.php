<?php

namespace App\Http\Api\Controllers\App;

use App\Http\Api\Requests\App\StoreMobileAppLogRequest;
use App\Http\Api\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\MobileAppLog;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

/**
 * @OA\Post(
 *     path="/api/app/logs",
 *     summary="Remonter un log d'erreur depuis une application mobile",
 *     description="Enregistre une erreur/crash survenu dans une app mobile pour visualisation dans le panneau admin. Non authentifié (les crashs surviennent souvent avant le login) ; si un token Bearer est fourni, l'utilisateur est attaché au log. Limité à 30 requêtes/minute.",
 *     operationId="api.app.logs.store",
 *     tags={"Application"},
 *
 *     @OA\RequestBody(
 *         required=true,
 *
 *         @OA\JsonContent(
 *             required={"app_type", "message"},
 *
 *             @OA\Property(property="app_type", type="string", enum={"customer_app", "delivery_app", "manager_app"}, example="customer_app"),
 *             @OA\Property(property="platform", type="string", enum={"android", "ios"}, nullable=true, example="android"),
 *             @OA\Property(property="app_version", type="string", nullable=true, example="1.0.11"),
 *             @OA\Property(property="device_model", type="string", nullable=true, example="Samsung SM-A125F"),
 *             @OA\Property(property="os_version", type="string", nullable=true, example="Android 13"),
 *             @OA\Property(property="level", type="string", enum={"error", "warning", "info"}, nullable=true, example="error", description="Défaut : error"),
 *             @OA\Property(property="message", type="string", maxLength=2000, example="type 'Null' is not a subtype of type 'String' in type cast"),
 *             @OA\Property(property="stack_trace", type="string", maxLength=20000, nullable=true),
 *             @OA\Property(property="context", type="object", nullable=true, example={"screen": "login", "endpoint": "/api/distribution-centers/closest"}),
 *             @OA\Property(property="occurred_at", type="string", format="date-time", nullable=true)
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=201,
 *         description="Log enregistré",
 *
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(
 *                 property="_metadata",
 *                 type="object",
 *                 @OA\Property(property="success", type="boolean", example=true),
 *                 @OA\Property(property="message", type="string", example="Log enregistré avec succès.")
 *             ),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="id", type="integer", example=1)
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=422,
 *         description="Erreur de validation",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
 *     )
 * )
 */
class StoreMobileAppLogController extends Controller
{
    /**
     * Store an error/diagnostic log entry reported by a mobile app,
     * so client-side crashes become visible from the admin panel.
     *
     * Route: POST /app/logs
     * Name: api.app.logs.store
     */
    public function __invoke(StoreMobileAppLogRequest $request): ApiResponse
    {
        $data = $request->validated();

        $log = MobileAppLog::create([
            ...$data,
            'level' => $data['level'] ?? 'error',
            'user_id' => $request->user('sanctum')?->id,
        ]);

        return ApiResponse::success(
            data: ['id' => $log->id],
            message: 'Log enregistré avec succès.',
            statusCode: JsonResponse::HTTP_CREATED
        );
    }
}
